<?php

declare(strict_types=1);

namespace BeechIt\NewsImporter\Service;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 12-05-2015 14:15
 * All code (c) Beech Applications B.V. all rights reserved
 */
use BeechIt\NewsImporter\Domain\Model\ExtractedItem;
use QueryPath\DOMQuery;
use QueryPath\Exception;
use TYPO3\CMS\Core\EventDispatcher\NoopEventDispatcher;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TypoScript\AST\AstBuilder;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\TypoScript\TypoScriptStringFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ExtractorService
 */
class ExtractorService implements SingletonInterface
{
    /**
     * @var ExtractedItem[]|null
     */
    protected ?array $items = null;

    /**
     * Remote url
     */
    protected string $source = '';

    /**
     * @var array<string, mixed>
     */
    protected array $itemMapping = [
        'items' => 'item',
        'item' => [
            'title' => 'title',
            'link' => 'link',
        ],
    ];

    protected ?string $rawContent = null;

    /**
     * Set source
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
        // reset rawContent && items
        $this->rawContent = null;
        $this->items = null;
    }

    public function getRawContent(): ?string
    {
        return $this->rawContent;
    }

    /**
     * Fetch URL content
     * Wrapper function so we can mock this for testing
     */
    public function fetchRawContent(string $source, ?string $postVars = null): string
    {
        if (is_file($source)) {
            return (string)file_get_contents($source);
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
            return (string)file_get_contents($source, false, $context);
        }
        return (string)GeneralUtility::getUrl($source);
    }

    /**
     * Extract value
     *
     * @param array<string, mixed> $mapping
     * @return string|array<int|string, mixed>
     */
    public function extractValue(DOMQuery $item, array $mapping, string $value = ''): string|array
    {
        if (empty($mapping['multiple'])) {
            /** @var DOMQuery $tmp */
            $tmp = $item->find($mapping['selector'])->first();
            $return = $this->_extractValue($tmp, $mapping, $value);
        } elseif (is_array($mapping['multiple'])) {
            $return = [];
            foreach ($item->find($mapping['selector']) as $tmp) {
                $rowValue = [];
                foreach ($mapping['multiple'] as $key => $subMapping) {
                    $rowValue[$key] = $this->_extractValue(
                        $tmp,
                        is_string($subMapping) ? ['attr' => $subMapping] : $subMapping,
                        is_string($subMapping) ? $subMapping : ''
                    );
                }
                $return[] = $rowValue;
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
     * @param array<string, mixed> $mapping
     */
    protected function _extractValue(DOMQuery $item, array $mapping, string $value = ''): string
    {
        if (!empty($mapping['attr'])) {
            $value = (string)$item->attr($mapping['attr']);
        } elseif (!empty($mapping['innerHTML'])) {
            $value = (string)$item->innerHTML();
        } else {
            $value = (string)$item->text();
        }
        $value = (string)mb_convert_encoding($value, 'UTF-8', 'auto');
        if (!empty($mapping['preg']) && preg_match((string)$mapping['preg'], $value, $matches)) {
            $value = $matches[1] ?? $matches[0];
        }
        if (!empty($mapping['wrap'])) {
            $value = str_replace('|', $value, (string)$mapping['wrap']);
        }
        if (!empty($mapping['strtotime'])) {
            $value = (string)strtotime($value);
        }
        if (!isset($mapping['trim']) || !empty($mapping['trim'])) {
            $value = trim($value);
        }
        return $value;
    }

    /**
     * Parse config
     *
     * @param string|array<string, mixed> $itemMapping
     * @return array<string, mixed>
     */
    protected function parseItemMapping(string|array $itemMapping): array
    {
        if (is_string($itemMapping)) {
            $typoScriptStringFactory = GeneralUtility::makeInstance(TypoScriptStringFactory::class);
            $rootNode = $typoScriptStringFactory->parseFromString(
                $itemMapping,
                GeneralUtility::makeInstance(AstBuilder::class, new NoopEventDispatcher())
            );
            $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
            $itemMapping = $typoScriptService->convertTypoScriptArrayToPlainArray($rootNode->toArray());
        }
        return $itemMapping;
    }

    public function stringToDOMQuery(string $string): DOMQuery
    {
        try {
            if (strtolower(substr($string, 0, 5)) === '<?xml') {
                $document = new \DOMDocument('1.0');
                if (@$document->loadXML(\QueryPath\Entities::replaceAllEntities($string), 0)) {
                    return qp($document);
                }
            }
            $domQuery = qp($string);
        } catch (Exception $e) {
            $domQuery = htmlqp($string);
        }
        return $domQuery;
    }

    /**
     * Set mapping configuration
     *
     * @param string|array<string, mixed> $itemMapping
     */
    public function setMapping(string|array $itemMapping): void
    {
        $this->itemMapping = $this->parseItemMapping($itemMapping);
    }

    /**
     * Extract items
     */
    protected function extractItems(): void
    {
        $this->items = [];
        $itemsSelector = !empty($this->itemMapping['items']) ? $this->itemMapping['items'] : 'item';

        if ($this->rawContent === null) {
            $this->rawContent = $this->fetchRawContent(
                $this->source,
                !empty($this->itemMapping['_POST']) ? (string)$this->itemMapping['_POST'] : null
            );
        }

        $domQuery = $this->stringToDOMQuery($this->rawContent);
        if (is_array($itemsSelector)) {
            if (!empty($itemsSelector['source'])) {
                $source = (string)$this->extractValue($domQuery, $itemsSelector['source']);
                $source = $this->fetchRawContent(
                    $source,
                    !empty($itemsSelector['source']['_POST']) ? (string)$itemsSelector['source']['_POST'] : null
                );
                $domQuery = $this->stringToDOMQuery($source);
            }
            $itemsSelector = !empty($itemsSelector['selector']) ? (string)$itemsSelector['selector'] : 'item';
        }

        foreach ($domQuery->find((string)$itemsSelector) as $item) {
            if ($item instanceof DOMQuery) {
                $this->items[] = new ExtractedItem(
                    clone $item,
                    is_array($this->itemMapping['item'] ?? null) ? $this->itemMapping['item'] : [],
                    $this
                );
            }
        }
    }

    /**
     * Get extracted news items
     *
     * @return ExtractedItem[]
     */
    public function getItems(): array
    {
        if ($this->items === null) {
            $this->extractItems();
        }
        return $this->items ?? [];
    }
}
