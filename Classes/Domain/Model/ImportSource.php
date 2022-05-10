<?php

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

/**
 * ImportSource
 */
class ImportSource extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * @var string
     */
    protected $title = '';

    /**
     * url
     *
     * @var string
     */
    protected $url = '';

    /**
     * mapping
     *
     * @var string
     */
    protected $mapping = '';

    /**
     * @var bool
     */
    protected $disableAutoImport = false;

    /**
     * lastRun
     *
     * @var \DateTime
     */
    protected $lastRun;

    /**
     * storagePid
     *
     * @var int
     */
    protected $storagePid = 0;

    /**
     * default image
     *
     * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    protected $defaultImage;

    /**
     * imageFolder
     *
     * @var string
     */
    protected $imageFolder = '';

    /**
     * @var string
     */
    protected $filter;

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Returns the url
     *
     * @return string $url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Sets the url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Returns the mapping
     *
     * @return string $mapping
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * Sets the mapping
     *
     * @param string $mapping
     */
    public function setMapping($mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * Get disableAutoImport
     *
     * @return bool
     */
    public function getDisableAutoImport()
    {
        return $this->disableAutoImport;
    }

    /**
     * Set disableAutoImport
     *
     * @param bool $disableAutoImport
     */
    public function setDisableAutoImport($disableAutoImport)
    {
        $this->disableAutoImport = $disableAutoImport;
    }

    /**
     * Returns the lastRun
     *
     * @return \DateTime $lastRun
     */
    public function getLastRun()
    {
        return $this->lastRun;
    }

    /**
     * Sets the lastRun
     *
     * @param \DateTime $lastRun
     */
    public function setLastRun(\DateTime $lastRun)
    {
        $this->lastRun = $lastRun;
    }

    /**
     * Returns the storagePid
     *
     * @return int $storagePid
     */
    public function getStoragePid()
    {
        return $this->storagePid;
    }

    /**
     * Sets the storagePid
     *
     * @param int $storagePid
     */
    public function setStoragePid($storagePid)
    {
        $this->storagePid = $storagePid;
    }

    /**
     * Get defaultImage
     *
     * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference
     */
    public function getDefaultImage()
    {
        return $this->defaultImage;
    }

    /**
     * Set defaultImage
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $defaultImage
     */
    public function setDefaultImage(FileReference $defaultImage = null)
    {
        $this->defaultImage = $defaultImage;
    }

    /**
     * Returns the imageFolder
     *
     * @return string $imageFolder
     */
    public function getImageFolder()
    {
        return $this->imageFolder;
    }

    /**
     * Sets the imageFolder
     *
     * @param string $imageFolder
     */
    public function setImageFolder($imageFolder)
    {
        $this->imageFolder = $imageFolder;
    }

    /**
     * Get filter
     *
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set filter
     *
     * @param string $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * Get filter words as a array
     *
     * @return array
     */
    public function getFilterWords()
    {
        return $this->filter ? GeneralUtility::trimExplode(',', $this->filter, true) : [];
    }
}
