<?php
namespace BeechIt\NewsImporter\Service;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 12-05-2015 14:15
 * All code (c) Beech Applications B.V. all rights reserved
 */
use BeechIt\NewsImporter\Domain\Model\ExtractedItem;
use TYPO3\CMS\Core\TypoScript\Parser\TypoScriptParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\TypoScriptService;

/**
 * Class ExtractorService
 */
class ExtractorService {

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
	protected $itemMapping = array(
		'items' => 'item',
		'item' => array(
			'title' => 'title',
			'link' => 'link'
		)
	);

	/**
	 * @var string
	 */
	protected $rawContent;

	/**
	 * Set source
	 *
	 * @param string $source
	 */
	public function setSource($source) {
		$this->source = $source;
	}

	/**
	 * Get URL content
	 * Wrapper function so we can mock this for testing
	 *
	 * @param string $source
	 * @param string $postVars
	 * @return string
	 */
	public function getRawContent($source, $postVars = NULL) {
		if (is_file($source)) {
			return file_get_contents($source);
		} elseif ($postVars !== NULL) {
			$options = array(
				'http' => array(
					'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
					'method'  => 'POST',
					'content' => $postVars,
				),
			);
			$context  = stream_context_create($options);
			return file_get_contents($source, false, $context);
		} else {
			return GeneralUtility::getUrl($source);
		}
	}

	/**
	 * @param $mapping
	 * @param string $value
	 * @return string
	 */
	public function extractValue(\QueryPath\DOMQuery $item, $mapping, $value = '') {

		/** @var \QueryPath\DOMQuery $tmp */
		$tmp = $item->find($mapping['selector'])->first();
		if ($tmp) {
			if (!empty($mapping['attr'])) {
				$value = $tmp->attr($mapping['attr']);
			} elseif (!empty($mapping['innerHTML'])) {
				$value = $tmp->innerHTML();
			} else {
				$value = $tmp->text();
			}
		}
		if (!empty($mapping['preg'])) {
			if (preg_match($mapping['preg'], $value, $matches)) {
				$value = isset($matches[1]) ? $matches[1] : $matches[0];
			}
		}
		if (!empty($mapping['wrap'])) {
			$value = str_replace('|', $value, $mapping['wrap']);
		}
		if (!empty($mapping['strtotime'])) {
			$value = strtotime($value);
		}
		return $value;
	}

	/**
	 * Parse config
	 *
	 * @param string|array $itemMapping
	 * @return array
	 */
	protected function parseItemMapping($itemMapping) {
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
	public function stringToDOMQuery($string) {
		try {
			$domQuery = qp($string);
		} catch (\QueryPath\Exception $e) {
			$domQuery = htmlqp($string);
		}
		return $domQuery;
	}

	/**
	 * Set mapping configuration
	 *
	 * @param string|array $itemMapping
	 */
	public function setMapping($itemMapping) {
		$this->itemMapping = $this->parseItemMapping($itemMapping);
	}

	/**
	 * Extract items
	 */
	protected function extractItems() {
		$this->items = array();
		$itemsSelector = !empty($this->itemMapping['items']) ? $this->itemMapping['items'] : 'item';

		if ($this->rawContent === NULL) {
			$this->rawContent = $this->getRawContent($this->source, !empty($this->itemMapping['_POST']) ? $this->itemMapping['_POST'] : NULL);
		}

		$domQuery = $this->stringToDOMQuery($this->rawContent);
		if (is_array($itemsSelector)) {
			if (!empty($itemsSelector['source'])) {
				$source = $this->extractValue($domQuery, $itemsSelector['source']);
				$source = $this->getRawContent($source, !empty($itemsSelector['source']['_POST']) ? $itemsSelector['source']['_POST'] : NULL);
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
	public function getItems() {
		if ($this->items === NULL) {
			$this->extractItems();
		}
		return $this->items;
	}
}