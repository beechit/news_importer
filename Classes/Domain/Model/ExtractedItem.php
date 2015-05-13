<?php
namespace BeechIt\NewsImporter\Domain\Model;

/**
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 13-05-2015 08:34
 * All code (c) Beech Applications B.V. all rights reserved
 */
use BeechIt\NewsImporter\Service\ExtractorService;
use QueryPath\DOMQuery;

/**
 * Class ExtractedItem
 */
class ExtractedItem {

	/**
	 * @var \QueryPath\DOMQuery
	 */
	protected $item;

	/**
	 * @var array
	 */
	protected $itemMapping;

	/**
	 * @var ExtractorService
	 */
	protected $extractorService;

	/**
	 * @var array
	 */
	protected $extractedValues = array();

	/**
	 * @param DOMQuery $item
	 * @param array $itemMapping
	 * @param ExtractorService $extractorService
	 */
	public function __construct(DOMQuery $item, array $itemMapping, ExtractorService $extractorService) {
		$this->item = $item;
		$this->itemMapping = $itemMapping;
		$this->extractorService = $extractorService;
	}

	/**
	 * Extract all fields/values
	 */
	protected function extractAll() {
		foreach ($this->itemMapping as $name => $mapping) {
			$this->extractValue($name);
		}
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function extractValue($name) {
		if (isset($this->extractedValues[$name])) {
			return $this->extractedValues[$name];
		}
		$mapping = isset($this->itemMapping[$name]) ? $this->itemMapping[$name] : $name;
		if (is_string($mapping)) {
			$mapping = array('selector' => $mapping);
		}
		$value = !empty($mapping['defaultValue']) ? empty($mapping['defaultValue']) : '';

		if (empty($mapping['selector']) && empty($mapping['defaultValue'])) {
			throw new \RuntimeException('Missing \'selector\' or \'defaultValue\' for ' . htmlentities($name) . ' mapping');
		}

		if (!empty($mapping['selector'])) {
			$value = $this->_extractvalue($mapping, $value);
		}

		$this->extractedValues[$name] = $value;

		return $this->extractedValues[$name];
	}

	/**
	 * @param $mapping
	 * @param string $value
	 * @return string
	 */
	protected function _extractvalue($mapping, $value = '') {
		/** @var \QueryPath\DOMQuery $tmp */
		$tmp = $this->item->find($mapping['selector'])->first();
		if ($tmp) {
			if (!empty($mapping['attr'])) {
				$value = $tmp->attr($mapping['attr']);
			} else {
				$value = $tmp->text();
			}
		}
		if (!empty($mapping['preg'])) {
			if (preg_match($mapping['preg'], $value, $matches)) {
				$value = $matches[0];
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
	 * Get guid
	 *
	 * @return string
	 */
	public function getGuid() {
		$guid = $this->extractValue('guid');
		if (empty($guid)) {
			$guid = $this->extractValue('link');
		}
		return $guid;
	}

	/**
	 * @return array
	 */
	public function toArray() {
		$this->extractAll();
		return $this->extractedValues;
	}
}