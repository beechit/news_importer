<?php

namespace BeechIt\NewsImporter\Service;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 12-06-2015
 * All code (c) Beech Applications B.V. all rights reserved
 */
use GeorgRinger\News\Domain\Service\NewsImportService;
use BeechIt\NewsImporter\Domain\Model\ExtractedItem;
use BeechIt\NewsImporter\Domain\Model\ImportSource;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ImportService
 */
class ImportService implements SingletonInterface
{

    /**
     * @var NewsImportService
     */
    protected $newsImportService;
    public function __construct(NewsImportService $newsImportService)
    {
        $this->newsImportService = $newsImportService;
    }

    /**
     * Import extracted item
     *
     * @param ImportSource $importSource
     * @param ExtractedItem $item
     */
    public function importItem(ImportSource $importSource, ExtractedItem $item)
    {
        $data = $item->toArray();
        $data['pid'] = $importSource->getStoragePid();
        $data['import_id'] = $item->getGuid();
        $data['import_source'] = 'ext:news_importer';

        // clean body text
        if (!empty($data['bodytext'])) {
            $data['bodytext'] = $this->cleanBodyText($data['bodytext'], $data['pid']);
        }

        // parse media
        $data['media'] = $this->processMedia($data, $importSource);

        $this->newsImportService->import([$data]);
    }

    /**
     * Check if news item already exists
     *
     * @param int $pid
     * @param string $guid
     * @return bool
     */
    public function alreadyImported($pid, $guid)
    {
        $guid = $this->getDatabaseConnection()->fullQuoteStr($guid, 'tx_news_domain_model_news');
        $record = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
            'uid',
            'tx_news_domain_model_news',
            'deleted=0 AND pid=' . (int)$pid . ' AND import_source = \'ext:news_importer\' AND import_id=' . $guid
        );
        return $record ? $record['uid'] : false;
    }

    /**
     * Check if item matches filter
     *
     * No $searchFields than item is aromatically excepted
     *
     * @param ExtractedItem $item
     * @param array $filterWords
     * @param array $searchFields
     * @return bool
     */
    public function matchFilter(ExtractedItem $item, $filterWords, array $searchFields = ['title', 'bodytext'])
    {
        if (empty($searchFields)) {
            return true;
        }

        $data = $item->toArray();
        foreach ($searchFields as $fieldName) {
            foreach ($filterWords as $filter) {
                if (stripos($data[$fieldName], $filter) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Clean body text by RTE settings
     *
     * @param string $text
     * @param int $pid
     */
    protected function cleanBodyText($text, $pid)
    {
        static $rteHtmlParsers;

        if (!isset($rteHtmlParsers[$pid])) {
            if (!is_array($rteHtmlParsers)) {
                $rteHtmlParsers = [];
            }
            /** @var $htmlParser \TYPO3\CMS\Core\Html\RteHtmlParser */
            $rteHtmlParsers[$pid] = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Html\\RteHtmlParser');
            $rteHtmlParsers[$pid]->init('tx_news_domain_model_news:bodytext', $pid);
        }

        // Perform transformation
        $tsConfig = BackendUtility::getPagesTSconfig($pid);
        return $rteHtmlParsers[$pid]->RTE_transform(
            trim($text),
            ['rte_transform' => ['parameters' => ['flag=rte_disabled', 'mode=ts_css']]],
            'db',
            $tsConfig['RTE.']['default.']
        );
    }

    /**
     * @param array $data
     * @param ImportSource $importSource
     * @return array|null
     */
    protected function processMedia(array $data, ImportSource $importSource)
    {
        $media = null;
        if (empty($data['image']) && $importSource->getDefaultImage()) {
            return [
                [
                    'type' => 0,
                    'image' => $importSource->getDefaultImage()->getOriginalResource()->getCombinedIdentifier(),
                    'showinpreview' => 1,
                ],
            ];
        }

        $folder = null;
        if ($importSource->getImageFolder()) {
            try {
                $folder = GeneralUtility::makeInstance(ResourceFactory::class)->getFolderObjectFromCombinedIdentifier(ltrim(
                    $importSource->getImageFolder(),
                    'file:'
                ));
            } catch (\Exception $e) {
            }
        }

        if ($folder && !empty($data['image'])) {
            $media = [];
            if (!is_array($data['image'])) {
                $data['image'] = [$data['image']];
            }
            foreach ($data['image'] as $image) {
                $tmp = GeneralUtility::getUrl($image);
                if ($tmp) {
                    $tempFile = GeneralUtility::tempnam('news_importer');
                    file_put_contents($tempFile, $tmp);
                    list(, , $imageType) = getimagesize($tempFile);
                    try {
                        $falImage = $folder->addFile(
                            $tempFile,
                            ($data['title'] ?: 'news_import') . image_type_to_extension($imageType, true),
                            DuplicationBehavior::RENAME
                        );
                        $media[] =
                            [
                                'type' => 0,
                                'image' => $falImage->getCombinedIdentifier(),
                                'showinpreview' => 1,
                            ];
                    } catch (\Exception $e) {
                    }
                }
            }
        }
        return $media;
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
