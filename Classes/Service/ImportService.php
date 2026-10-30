<?php

declare(strict_types=1);

namespace BeechIt\NewsImporter\Service;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 12-06-2015
 * All code (c) Beech Applications B.V. all rights reserved
 */

use BeechIt\NewsImporter\Domain\Model\ExtractedItem;
use BeechIt\NewsImporter\Domain\Model\ImportSource;
use BeechIt\NewsImporter\Exception\NewsItemNotFoundException;
use GeorgRinger\News\Domain\Service\NewsImportService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Html\RteHtmlParser;
use TYPO3\CMS\Core\Resource\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ImportService
 */
class ImportService implements SingletonInterface
{
    protected NewsImportService $newsImportService;

    protected ConnectionPool $connectionPool;

    protected ResourceFactory $resourceFactory;

    public function __construct(
        NewsImportService $newsImportService,
        ?ConnectionPool $connectionPool = null,
        ?ResourceFactory $resourceFactory = null
    ) {
        $this->newsImportService = $newsImportService;
        $this->connectionPool = $connectionPool ?? GeneralUtility::makeInstance(ConnectionPool::class);
        $this->resourceFactory = $resourceFactory ?? GeneralUtility::makeInstance(ResourceFactory::class);
    }

    /**
     * Import extracted item
     */
    public function importItem(ImportSource $importSource, ExtractedItem $item): void
    {
        $data = $item->toArray();
        $data['pid'] = $importSource->getStoragePid();
        $data['import_id'] = $item->getGuid();
        $data['import_source'] = 'ext:news_importer';

        // clean body text
        if (!empty($data['bodytext'])) {
            $data['bodytext'] = $this->cleanBodyText((string)$data['bodytext'], $data['pid']);
        }

        // parse media
        $data['media'] = $this->processMedia($data, $importSource);

        $this->newsImportService->import([$data]);
    }

    /**
     * Check if news item already exists
     */
    public function alreadyImported(int $pid, string $guid): bool
    {
        $newsItemImported = false;
        try {
            $this->getNewsItemUid($pid, $guid);
            $newsItemImported = true;
        } catch (NewsItemNotFoundException $e) {
        }
        return $newsItemImported;
    }

    /**
     * @throws NewsItemNotFoundException
     */
    public function getNewsItemUid(int $pid, string $guid): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_news_domain_model_news');
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $firstRecord = $queryBuilder->select('uid')
            ->from('tx_news_domain_model_news')
            ->where(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter($pid, Connection::PARAM_INT)
                )
            )
            ->andWhere(
                $queryBuilder->expr()->eq(
                    'import_source',
                    $queryBuilder->createNamedParameter('ext:news_importer', Connection::PARAM_STR)
                )
            )->andWhere($queryBuilder->expr()->eq(
                'import_id',
                $queryBuilder->createNamedParameter($guid, Connection::PARAM_STR)
            ))->executeQuery()
            ->fetchAssociative();

        if ($firstRecord === false) {
            throw new NewsItemNotFoundException($guid);
        }
        return (int)$firstRecord['uid'];
    }

    /**
     * Check if item matches filter
     *
     * No $searchFields than item is automatically accepted
     *
     * @param list<string> $filterWords
     * @param list<string> $searchFields
     */
    public function matchFilter(ExtractedItem $item, array $filterWords, array $searchFields = ['title', 'bodytext']): bool
    {
        if (empty($searchFields)) {
            return true;
        }

        $data = $item->toArray();
        foreach ($searchFields as $fieldName) {
            foreach ($filterWords as $filter) {
                if (stripos((string)($data[$fieldName] ?? ''), $filter) !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Clean body text by RTE settings
     */
    protected function cleanBodyText(string $text, int $pid): string
    {
        /** @var array<int, RteHtmlParser> $rteHtmlParsers */
        static $rteHtmlParsers = [];

        if (!isset($rteHtmlParsers[$pid])) {
            $rteHtmlParsers[$pid] = GeneralUtility::makeInstance(RteHtmlParser::class);
        }

        // Perform transformation
        $tsConfig = BackendUtility::getPagesTSconfig($pid);
        return $rteHtmlParsers[$pid]->transformTextForPersistence(
            trim($text),
            $tsConfig['RTE.']['default.']['proc.'] ?? []
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return list<array<string, int|string>>|null
     */
    protected function processMedia(array $data, ImportSource $importSource): ?array
    {
        $media = null;
        $defaultImage = $importSource->getDefaultImage();
        if (empty($data['image']) && $defaultImage !== null) {
            return [
                [
                    'type' => 0,
                    'image' => $defaultImage->getOriginalResource()->getCombinedIdentifier(),
                    'showinpreview' => 1,
                ],
            ];
        }

        $folder = null;
        if ($importSource->getImageFolder() !== '') {
            try {
                $folder = $this->resourceFactory->getFolderObjectFromCombinedIdentifier(ltrim(
                    $importSource->getImageFolder(),
                    'file:'
                ));
            } catch (\Exception $e) {
            }
        }

        if ($folder !== null && !empty($data['image'])) {
            $media = [];
            if (!is_array($data['image'])) {
                $data['image'] = [$data['image']];
            }
            foreach ($data['image'] as $image) {
                $tmp = GeneralUtility::getUrl((string)$image);
                if ($tmp) {
                    $tempFile = GeneralUtility::tempnam('news_importer');
                    file_put_contents($tempFile, $tmp);
                    $imageInfo = @getimagesize($tempFile);
                    $imageType = $imageInfo !== false ? (int)$imageInfo[2] : IMAGETYPE_JPEG;
                    try {
                        $falImage = $folder->addFile(
                            $tempFile,
                            ((string)($data['title'] ?? '') ?: 'news_import') . image_type_to_extension($imageType, true),
                            DuplicationBehavior::RENAME
                        );
                        $media[] = [
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
}
