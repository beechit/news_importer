<?php

namespace BeechIt\NewsImporter\Tests\Functional\Service;

use BeechIt\NewsImporter\Service\ExtractorService;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Class NewsMapperServiceTest
 */
class ExtractorServiceTest extends FunctionalTestCase
{

    /**
     * @var ExtractorService
     */
    protected $extractorService;

    protected $testExtensionsToLoad = ['typo3conf/ext/news_importer'];

    public function setUp()
    {
        parent::setUp();
        $this->extractorService = new ExtractorService();
    }

    /**
     * @test
     */
    public function basicRssFeedImportTest()
    {
        $this->extractorService->setSource(__DIR__ . '/../Fixtures/RemoteData/rss.xml');
        $this->extractorService->setMapping([
            'items' => 'item',
            'item' => [
                'title' => 'title',
                'link' => 'link',
                'pubDate' => [
                    'selector' => 'pubDate',
                    'strtotime' => 1,
                ],
            ],
        ]);
        $items = $this->extractorService->getItems();
        self::assertCount(10, $items);
        self::assertEquals(
            'Middelburg verwijdert verkeerd geparkeerde fietsen op station   ',
            $items[0]->extractValue('title')
        );
        self::assertEquals(
            'http://www.nu.nl/walcheren/4048130/middelburg-verwijdert-verkeerd-geparkeerde-fietsen-station-.html',
            $items[0]->extractValue('link')
        );
        self::assertEquals(1431452309, $items[0]->extractValue('pubDate'));
    }

    /**
     * @test
     */
    public function rssFeedWithCustomNamespaceImportTest()
    {
        $this->extractorService->setSource(__DIR__ . '/../Fixtures/RemoteData/rss2.xml');
        $this->extractorService->setMapping([
            'items' => 'item',
            'item' => [
                'title' => 'title',
                'link' => 'link',
                'datetime' => [
                    'selector' => 'date', // real tag = dc:date namespace can be dropped
                    'strtotime' => 1,
                ],
                'image' => [
                    'selector' => 'leadimage', // real tag = agsci:leadimage namespace can be dropped
                    'attr' => 'url',
                ],
            ],
        ]);
        $items = $this->extractorService->getItems();
        self::assertCount(25, $items);
        self::assertEquals('Flea Beetle Management', $items[0]->extractValue('title'));
        self::assertEquals(
            'http://extension.psu.edu/plants/vegetable-fruit/news/2015/flea-beetle-management',
            $items[0]->extractValue('link')
        );
        self::assertEquals(1433188535, $items[0]->extractValue('datetime'));
        self::assertEquals(
            'http://extension.psu.edu/plants/vegetable-fruit/news/2015/flea-beetle-management/image',
            $items[0]->extractValue('image')
        );
    }

    /**
     * @test
     */
    public function xmlImportWithMultipleValuesTest()
    {
        $this->extractorService->setSource(__DIR__ . '/../Fixtures/RemoteData/custom.xml');
        $this->extractorService->setMapping([
            'items' => 'item',
            'item' => [
                'title' => 'title',
                'link' => 'link',
                'datetime' => [
                    'selector' => 'pubDate',
                    'strtotime' => 1,
                ],
                'related_links' => [
                    'selector' => 'related_link',
                    'multiple' => [
                        'uri' => ['attr' => 'href'], // method 1 to get attribute value
                        'title' => 'title', // method 2 to get a attribute value
                    ],
                ],
                'image' => [
                    'selector' => 'enclosure',
                    'multiple' => 1,
                    'attr' => 'url',
                ],
            ],
        ]);
        $items = $this->extractorService->getItems();
        self::assertCount(10, $items);
        self::assertEquals(
            'Middelburg verwijdert verkeerd geparkeerde fietsen op station',
            $items[0]->extractValue('title')
        );
        self::assertEquals(
            'http://www.nu.nl/walcheren/4048130/middelburg-verwijdert-verkeerd-geparkeerde-fietsen-station-.html',
            $items[0]->extractValue('link')
        );
        self::assertEquals(1431452309, $items[0]->extractValue('datetime'));

        $images = $items[0]->extractValue('image');
        self::assertCount(2, $images);
        self::assertEquals(
            'http://media.nu.nl/m/m1oxhrpa8daz_sqr256.jpg/middelburg-verwijdert-verkeerd-geparkeerde-fietsen-station-.jpg',
            $images[0]
        );

        $relatedLinks = $items[0]->extractValue('related_links');
        self::assertCount(2, $relatedLinks);
        self::assertEquals(
            'http://www.nu.nl/walcheren/4048130/middelburg-verwijdert-verkeerd-geparkeerde-fietsen-station-.html',
            $relatedLinks[0]['uri']
        );
        self::assertEquals('Extra link 1', $relatedLinks[0]['title']);
    }
}
