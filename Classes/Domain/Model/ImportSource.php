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
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

/**
 * ImportSource
 */
class ImportSource extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

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
	 * lastRun
	 *
	 * @var \DateTime
	 */
	protected $lastRun = NULL;

	/**
	 * storagePid
	 *
	 * @var integer
	 */
	protected $storagePid = 0;

	/**
	 * default image
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Model\FileReference
	 */
	protected $defaultImage = NULL;

	/**
	 * imageFolder
	 *
	 * @var string
	 */
	protected $imageFolder = '';

	/**
	 * Returns the url
	 *
	 * @return string $url
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * Sets the url
	 *
	 * @param string $url
	 * @return void
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

	/**
	 * Returns the mapping
	 *
	 * @return string $mapping
	 */
	public function getMapping() {
		return $this->mapping;
	}

	/**
	 * Sets the mapping
	 *
	 * @param string $mapping
	 * @return void
	 */
	public function setMapping($mapping) {
		$this->mapping = $mapping;
	}

	/**
	 * Returns the lastRun
	 *
	 * @return \DateTime $lastRun
	 */
	public function getLastRun() {
		return $this->lastRun;
	}

	/**
	 * Sets the lastRun
	 *
	 * @param \DateTime $lastRun
	 * @return void
	 */
	public function setLastRun(\DateTime $lastRun) {
		$this->lastRun = $lastRun;
	}

	/**
	 * Returns the storagePid
	 *
	 * @return integer $storagePid
	 */
	public function getStoragePid() {
		return $this->storagePid;
	}

	/**
	 * Sets the storagePid
	 *
	 * @param integer $storagePid
	 * @return void
	 */
	public function setStoragePid($storagePid) {
		$this->storagePid = $storagePid;
	}

	/**
	 * Get defaultImage
	 *
	 * @return \TYPO3\CMS\Extbase\Domain\Model\FileReference
	 */
	public function getDefaultImage() {
		return $this->defaultImage;
	}

	/**
	 * Set defaultImage
	 *
	 * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $defaultImage
	 */
	public function setDefaultImage(FileReference $defaultImage = NULL) {
		$this->defaultImage = $defaultImage;
	}

	/**
	 * Returns the imageFolder
	 *
	 * @return string $imageFolder
	 */
	public function getImageFolder() {
		return $this->imageFolder;
	}

	/**
	 * Sets the imageFolder
	 *
	 * @param string $imageFolder
	 * @return void
	 */
	public function setImageFolder($imageFolder) {
		$this->imageFolder = $imageFolder;
	}

}