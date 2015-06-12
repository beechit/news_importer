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
		$value = !empty($mapping['defaultValue']) ? $mapping['defaultValue'] : '';

		if (empty($mapping['selector']) && empty($mapping['defaultValue'])) {
			throw new \RuntimeException('Missing \'selector\' or \'defaultValue\' for ' . htmlentities($name) . ' mapping');
		}

		if (!empty($mapping['selector'])) {
			if (!empty($mapping['source'])) {
				$source = $this->extractorService->extractValue($this->item, $mapping['source']);
				$source = $this->extractorService->fetchRawContent($source);
				try {
					$item = qp($source);
				} catch (\QueryPath\Exception $e) {
					$item = htmlqp($source);
				}
			} else {
				$item = $this->item;
			}
			$value = $this->extractorService->extractValue($item, $mapping, $value);
		}

		$this->extractedValues[$name] = $value;

		return $this->extractedValues[$name];
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
		// `import_id` db field is varchar(100)
		if (strlen($guid) > 90) {
			$guid = sha1($guid);
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