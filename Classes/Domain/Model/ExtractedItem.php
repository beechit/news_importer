<?php

declare(strict_types=1);

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
class ExtractedItem
{
    protected DOMQuery $item;

    /**
     * @var array<string, mixed>
     */
    protected array $itemMapping;

    protected ExtractorService $extractorService;

    /**
     * @var array<string, mixed>
     */
    protected array $extractedValues = [];

    /**
     * @param array<string, mixed> $itemMapping
     */
    public function __construct(DOMQuery $item, array $itemMapping, ExtractorService $extractorService)
    {
        $this->item = $item;
        $this->itemMapping = $itemMapping;
        $this->extractorService = $extractorService;
    }

    /**
     * Extract all fields/values
     */
    protected function extractAll(): void
    {
        foreach ($this->itemMapping as $name => $mapping) {
            $this->extractValue((string)$name);
        }
    }

    public function extractValue(string $name): mixed
    {
        if (isset($this->extractedValues[$name])) {
            return $this->extractedValues[$name];
        }
        $mapping = $this->itemMapping[$name] ?? $name;
        if (is_string($mapping)) {
            $mapping = ['selector' => $mapping];
        }
        $value = !empty($mapping['defaultValue']) ? $mapping['defaultValue'] : '';

        if (empty($mapping['selector']) && empty($mapping['defaultValue'])) {
            throw new \RuntimeException('Missing \'selector\' or \'defaultValue\' for ' . htmlentities($name) . ' mapping');
        }

        if (!empty($mapping['selector'])) {
            if (!empty($mapping['source'])) {
                $source = (string)$this->extractorService->extractValue($this->item, $mapping['source']);
                $source = $this->extractorService->fetchRawContent($source);
                $item = $this->extractorService->stringToDOMQuery($source);
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
     */
    public function getGuid(): string
    {
        $guid = (string)$this->extractValue('guid');
        if (empty($guid)) {
            $guid = (string)$this->extractValue('link');
        }
        // `import_id` db field is varchar(100)
        if (strlen($guid) > 90) {
            $guid = sha1($guid);
        }
        return $guid;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $this->extractAll();
        return $this->extractedValues;
    }
}
