<?php

namespace BeechIt\NewsImporter\Tests\Unit\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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

/**
 * Test case for class \BeechIt\NewsImporter\Domain\Model\ImportSource.
 *
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class ImportSourceTest extends \TYPO3\CMS\Core\Tests\UnitTestCase {
	/**
	 * @var \BeechIt\NewsImporter\Domain\Model\ImportSource
	 */
	protected $subject = NULL;

	protected function setUp() {
		$this->subject = new \BeechIt\NewsImporter\Domain\Model\ImportSource();
	}

	protected function tearDown() {
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function getUrlReturnsInitialValueForString() {
		$this->assertSame(
			'',
			$this->subject->getUrl()
		);
	}

	/**
	 * @test
	 */
	public function setUrlForStringSetsUrl() {
		$this->subject->setUrl('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'url',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getMappingReturnsInitialValueForString() {
		$this->assertSame(
			'',
			$this->subject->getMapping()
		);
	}

	/**
	 * @test
	 */
	public function setMappingForStringSetsMapping() {
		$this->subject->setMapping('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'mapping',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getLastRunReturnsInitialValueForDateTime() {
		$this->assertEquals(
			NULL,
			$this->subject->getLastRun()
		);
	}

	/**
	 * @test
	 */
	public function setLastRunForDateTimeSetsLastRun() {
		$dateTimeFixture = new \DateTime();
		$this->subject->setLastRun($dateTimeFixture);

		$this->assertAttributeEquals(
			$dateTimeFixture,
			'lastRun',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getStoragePidReturnsInitialValueForInteger() {
		$this->assertSame(
			0,
			$this->subject->getStoragePid()
		);
	}

	/**
	 * @test
	 */
	public function setStoragePidForIntegerSetsStoragePid() {
		$this->subject->setStoragePid(12);

		$this->assertAttributeEquals(
			12,
			'storagePid',
			$this->subject
		);
	}

	/**
	 * @test
	 */
	public function getImageFolderReturnsInitialValueForString() {
		$this->assertSame(
			'',
			$this->subject->getImageFolder()
		);
	}

	/**
	 * @test
	 */
	public function setImageFolderForStringSetsImageFolder() {
		$this->subject->setImageFolder('Conceived at T3CON10');

		$this->assertAttributeEquals(
			'Conceived at T3CON10',
			'imageFolder',
			$this->subject
		);
	}
}
