<?php
namespace BeechIt\NewsImporter\Tests\Functional\Service;

/**
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 12-05-2015 14:26
 * All code (c) Beech Applications B.V. all rights reserved
 */
use BeechIt\NewsImporter\Service\ExtractorService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class NewsMapperServiceTest
 */
class ExtractorServiceTest extends \TYPO3\CMS\Core\Tests\FunctionalTestCase {

	/**
	 * @var ExtractorService
	 */
	protected $extractorService;

	protected $testExtensionsToLoad = array('typo3conf/ext/news_importer');

	public function setUp() {
		parent::setUp();
		$this->extractorService = new ExtractorService();
	}

	/**
	 * @test
	 */
	public function basicRssFeedImportTest() {
		$this->extractorService->setSource(__DIR__ . '/../Fixtures/RemoteData/rss.xml');
		$this->extractorService->setMapping(array(
			'items' => 'item',
			'item' => array(
				'title' => 'title',
				'link' => 'link',
				'pubDate' => array(
					'selector' => 'pubDate',
					'strtotime' => 1
				)
			)
		));
		$items = $this->extractorService->getItems();
		$this->assertEquals(10, count($items));
		$this->assertEquals('Middelburg verwijdert verkeerd geparkeerde fietsen op station', $items[0]->extractValue('title'));
		$this->assertEquals('http://www.nu.nl/walcheren/4048130/middelburg-verwijdert-verkeerd-geparkeerde-fietsen-station-.html', $items[0]->extractValue('link'));
		$this->assertEquals(1431452309, $items[0]->extractValue('pubDate'));
	}
}