<?php

declare(strict_types=1);

namespace BeechIt\NewsImporter\Domain\Model;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * ImportSource
 */
class ImportSource extends AbstractEntity
{
    protected string $title = '';

    protected string $url = '';

    protected string $mapping = '';

    protected bool $disableAutoImport = false;

    protected ?\DateTime $lastRun = null;

    protected int $storagePid = 0;

    protected ?FileReference $defaultImage = null;

    protected string $imageFolder = '';

    protected ?string $filter = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getMapping(): string
    {
        return $this->mapping;
    }

    public function setMapping(string $mapping): void
    {
        $this->mapping = $mapping;
    }

    public function getDisableAutoImport(): bool
    {
        return $this->disableAutoImport;
    }

    public function setDisableAutoImport(bool $disableAutoImport): void
    {
        $this->disableAutoImport = $disableAutoImport;
    }

    public function getLastRun(): ?\DateTime
    {
        return $this->lastRun;
    }

    public function setLastRun(?\DateTime $lastRun): void
    {
        $this->lastRun = $lastRun;
    }

    public function getStoragePid(): int
    {
        return $this->storagePid;
    }

    public function setStoragePid(int $storagePid): void
    {
        $this->storagePid = $storagePid;
    }

    public function getDefaultImage(): ?FileReference
    {
        return $this->defaultImage;
    }

    public function setDefaultImage(?FileReference $defaultImage = null): void
    {
        $this->defaultImage = $defaultImage;
    }

    public function getImageFolder(): string
    {
        return $this->imageFolder;
    }

    public function setImageFolder(string $imageFolder): void
    {
        $this->imageFolder = $imageFolder;
    }

    public function getFilter(): string
    {
        return (string)$this->filter;
    }

    public function setFilter(?string $filter): void
    {
        $this->filter = (string)$filter;
    }

    /**
     * Get filter words as an array
     *
     * @return list<string>
     */
    public function getFilterWords(): array
    {
        return $this->filter ? GeneralUtility::trimExplode(',', $this->filter, true) : [];
    }
}
