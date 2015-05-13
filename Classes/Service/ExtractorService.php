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
		$this->rawContent = $this->getRawContent($source);
	}

	/**
	 * Get URL content
	 * Wrapper function so we can mock this for testing
	 *
	 * @param string $source
	 * @return string
	 */
	protected function getRawContent($source) {
		if (is_file($source)) {
			return file_get_contents($source);
		} else {
			return GeneralUtility::getUrl($source);
		}
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
		try {
			$items = qp($this->rawContent, $itemsSelector);
		} catch (\QueryPath\Exception $e) {
			$items = htmlqp($this->rawContent, $itemsSelector);
		}
		/** @var \QueryPath\DOMQuery $item */
		foreach ($items as $item) {
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