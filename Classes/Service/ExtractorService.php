<?php

namespace BeechIt\NewsImporter\Service;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 12-05-2015 14:15
 * All code (c) Beech Applications B.V. all rights reserved
 */
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use QueryPath\DOMQuery;
use QueryPath\Exception;
use BeechIt\NewsImporter\Domain\Model\ExtractedItem;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ExtractorService
 */
class ExtractorService implements SingletonInterface
{

    /**
     * @var array
     */
    protected $items;

    /**
     * Remote url
     *
     * @var string
     */
    protected $source = '';

    /**
     * @var array
     */
    protected $itemMapping = [
        'items' => 'item',
        'item' => [
            'title' => 'title',
            'link' => 'link',
        ],
    ];

    /**
     * @var string
     */
    protected $rawContent;

    /**
     * Set source
     *
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
        // reset rawContent && items
        $this->rawContent = null;
        $this->items = null;
    }

    /**
     * @return string
     */
    public function getRawContent()
    {
        return $this->rawContent;
    }

    /**
     * Fetch URL content
     * Wrapper function so we can mock this for testing
     *
     * @param string $source
     * @param string $postVars
     * @return string
     */
    public function fetchRawContent($source, $postVars = null)
    {
        if (is_file($source)) {
            return file_get_contents($source);
        }
        if ($postVars !== null) {
            $options = [
                'http' => [
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => $postVars,
                ],
            ];
            $context = stream_context_create($options);
            return file_get_contents($source, false, $context);
        }
        return GeneralUtility::getUrl($source);
    }

    /**
     * Extract value
     *
     * @param \QueryPath\DOMQuery $item
     * @param array $mapping
     * @param string $value
     * @return string|array
     */
    public function extractValue(DOMQuery $item, array $mapping, $value = '')
    {
        if (empty($mapping['multiple'])) {
            /** @var \QueryPath\DOMQuery $tmp */
            $tmp = $item->find($mapping['selector'])->first();
            $return = $this->_extractValue($tmp, $mapping, $value);
        } elseif (is_array($mapping['multiple'])) {
            $return = [];
            foreach ($item->find($mapping['selector']) as $tmp) {
                $value = [];
                foreach ($mapping['multiple'] as $key => $subMapping) {
                    $value[$key] = $this->_extractValue(
                        $tmp,
                        is_string($subMapping) ? ['attr' => $subMapping] : $subMapping,
                        is_string($subMapping) ? $subMapping : ''
                    );
                }
                $return[] = $value;
            }
        } else {
            $return = [];
            foreach ($item->find($mapping['selector']) as $tmp) {
                $return[] = $this->_extractValue($tmp, $mapping, $value);
            }
        }
        return $return;
    }

    /**
     * Extract a single value
     *
     * @param \QueryPath\DOMQuery $item
     * @param array $mapping
     * @param string $value
     * @return string
     */
    protected function _extractValue(DOMQuery $item, array $mapping, $value = '')
    {
        if ($item) {
            if (!empty($mapping['attr'])) {
                $value = $item->attr($mapping['attr']);
            } elseif (!empty($mapping['innerHTML'])) {
                $value = $item->innerHTML();
            } else {
                $value = $item->text();
            }
        }
        $value = mb_convert_encoding($value, 'UTF-8', 'auto');
        if (!empty($mapping['preg']) && preg_match($mapping['preg'], $value, $matches)) {
            $value = isset($matches[1]) ? $matches[1] : $matches[0];
        }
        if (!empty($mapping['wrap'])) {
            $value = str_replace('|', $value, $mapping['wrap']);
        }
        if (!empty($mapping['strtotime'])) {
            $value = strtotime($value);
        }
        if (!isset($mapping['trim']) || !empty($mapping['trim'])) {
            $value = trim($value);
        }
        return $value;
    }

    /**
     * Parse config
     *
     * @param string|array $itemMapping
     * @return array
     */
    protected function parseItemMapping($itemMapping)
    {
        if (is_string($itemMapping)) {
            /** @var TypoScriptParser $parser */
            $parser = GeneralUtility::makeInstance(TypoScriptParser::class);
            $parser->parse($itemMapping);
            /** @var $typoScriptService TypoScriptService */
            $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
            $itemMapping = $typoScriptService->convertTypoScriptArrayToPlainArray($parser->setup);
        }
        return $itemMapping;
    }

    /**
     * @param string $string
     * @return \QueryPath\DOMQuery
     */
    public function stringToDOMQuery($string)
    {
        try {
            $domQuery = qp($string);
        } catch (Exception $e) {
            $domQuery = htmlqp($string);
        }
        return $domQuery;
    }

    /**
     * Set mapping configuration
     *
     * @param string|array $itemMapping
     */
    public function setMapping($itemMapping)
    {
        $this->itemMapping = $this->parseItemMapping($itemMapping);
    }

    /**
     * Extract items
     */
    protected function extractItems()
    {
        $this->items = [];
        $itemsSelector = !empty($this->itemMapping['items']) ? $this->itemMapping['items'] : 'item';

        if ($this->rawContent === null) {
            $this->rawContent = $this->fetchRawContent(
                $this->source,
                !empty($this->itemMapping['_POST']) ? $this->itemMapping['_POST'] : null
            );
        }

        $domQuery = $this->stringToDOMQuery($this->rawContent);
        if (is_array($itemsSelector)) {
            if (!empty($itemsSelector['source'])) {
                $source = $this->extractValue($domQuery, $itemsSelector['source']);
                $source = $this->fetchRawContent(
                    $source,
                    !empty($itemsSelector['source']['_POST']) ? $itemsSelector['source']['_POST'] : null
                );
                $domQuery = $this->stringToDOMQuery($source);
            }
            $itemsSelector = !empty($itemsSelector['selector']) ? $itemsSelector['selector'] : 'item';
        }

        /** @var \QueryPath\DOMQuery $item */
        foreach ($domQuery->find($itemsSelector) as $item) {
            $this->items[] = new ExtractedItem(clone $item, $this->itemMapping['item'], $this);
        }
    }

    /**
     * Get extracted news items
     *
     * @return ExtractedItem[]
     */
    public function getItems()
    {
        if ($this->items === null) {
            $this->extractItems();
        }
        return $this->items;
    }
}
