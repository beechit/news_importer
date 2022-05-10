<?php

namespace BeechIt\NewsImporter\Command;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 12-05-2015 15:21
 * All code (c) Beech Applications B.V. all rights reserved
 */
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use BeechIt\NewsImporter\Domain\Repository\ImportSourceRepository;
use BeechIt\NewsImporter\Service\ExtractorService;
use BeechIt\NewsImporter\Service\ImportService;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use BeechIt\NewsImporter\Domain\Model\ImportSource;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Class ImportNewsCommand controller
 */
class ImportNewsCommandController extends CommandController
{

    /**
     * Define maximum line length of the terminal
     */
    const MAXIMUM_LINE_LENGTH = 29;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @var ImportSourceRepository
     */
    protected $importSourceRepository;

    /**
     * @var ExtractorService
     */
    protected $extractorService;

    /**
     * @var ImportService
     */
    protected $importService;
    public function __construct(ConfigurationManagerInterface $configurationManager, PersistenceManager $persistenceManager, ImportSourceRepository $importSourceRepository, ExtractorService $extractorService, ImportService $importService)
    {
        $this->configurationManager = $configurationManager;
        $this->persistenceManager = $persistenceManager;
        $this->importSourceRepository = $importSourceRepository;
        $this->extractorService = $extractorService;
        $this->importService = $importService;
    }

    /**
     * Call command
     */
    protected function callCommandMethod()
    {
        $this->settings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'newsImporter'
        );
        /** @var StorageRepository $storageRepository */
        $storageRepository = $this->objectManager->get(StorageRepository::class);
        foreach ($storageRepository->findAll() as $storage) {
            $storage->setEvaluatePermissions(false);
        }
        parent::callCommandMethod();
    }

    /**
     * Get status of all defined remotes (last run datetime)
     */
    public function statusCommand()
    {
        $this->outputDashedLine();
        $remotes = $this->importSourceRepository->findAll();
        /** @var ImportSource $remote */
        foreach ($remotes as $remote) {
            $lastRun = '';
            if ($remote->getDisableAutoImport()) {
                $lastRun = ' - auto import is disabled!';
            } elseif ($remote->getLastRun()) {
                $lastRun = ' - last run: ' . $remote->getLastRun()->format('Y-m-d H:i:s');
            }
            $this->outputLine('[' . $remote->getUid() . '] ' . $remote->getUrl() . $lastRun);
        }
        if ($remotes->count() === 0) {
            $this->outputLine('No remotes found!');
        }
        $this->outputDashedLine();
        $this->outputLine('settings: ' . ($this->settings ? print_r($this->settings, 1) : 'NO TYPOSCRIPT SETTINGS'));
        $this->outputDashedLine();
    }

    /**
     * Run importer
     *
     * @param int $limit number of sources to check
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function runCommand($limit = 1)
    {
        $importSources = $this->importSourceRepository->findSourcesToImport($limit);
        $importReport = [];
        if (isset($this->settings['filter']['searchFields']) && is_array($this->settings['filter']['searchFields'])) {
            $searchFields = $this->settings['filter']['searchFields'];
        } else {
            $searchFields = ['title', 'bodytext'];
        }

        $this->outputLine();

        /** @var ImportSource $importSource */
        foreach ($importSources as $importSource) {
            $this->outputLine($importSource->getTitle());
            $this->outputDashedLine();

            $this->extractorService->setSource($importSource->getUrl());
            $this->extractorService->setMapping($importSource->getMapping());
            $items = $this->extractorService->getItems();

            foreach ($items as $item) {
                if ($this->importService->alreadyImported($importSource->getStoragePid(), $item->getGuid())) {
                    $this->outputLine('Already imported: ' . $item->getGuid());
                } elseif ($importSource->getFilterWords() && !$this->importService->matchFilter(
                    $item,
                    $importSource->getFilterWords(),
                    $searchFields
                )) {
                    $this->outputLine('Skipped: ' . $item->getGuid() . '; Filter mismatch');
                } else {
                    $this->importService->importItem($importSource, $item);
                    $this->outputLine('Imported: ' . $item->getGuid());
                    $importReport[] = $item->extractValue('title') . '; ' . $item->getGuid();
                }
            }

            if (!$items) {
                $this->outputLine('No items found');
            }
            $importSource->setLastRun(new \DateTime());
            $this->importSourceRepository->update($importSource);
            $this->persistenceManager->persistAll();
        }

        if ($importReport !== [] && !empty($this->settings['notification']['recipients'])) {
            /** @var MailMessage $message */
            $message = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Mail\\MailMessage');
            $message->setTo($this->settings['notification']['recipients'])
                ->setSubject($this->settings['notification']['subject'] ?: 'New items imported');
            if ($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress']) {
                $message->setFrom(
                    $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'],
                    $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] ?: null
                );
            }
            $message->setBody(
                vsprintf(
                    $this->settings['notification']['body'] ?: 'Imported %1$d items: %2$s',
                    [
                        count($importReport),
                        PHP_EOL . PHP_EOL . implode(PHP_EOL, $importReport),
                    ]
                )
            );
            $message->send();
        }
    }

    /**
     * Test import source by counting found items and displaying data of first item
     *
     * @param ImportSource $importSource
     */
    public function testSourceCommand(ImportSource $importSource)
    {
        $this->outputLine('Fetch: ' . $importSource->getUrl());
        $this->outputDashedLine();

        $this->extractorService->setSource($importSource->getUrl());
        $this->extractorService->setMapping($importSource->getMapping());
        $items = $this->extractorService->getItems();

        $this->outputLine('Found ' . count($items) . ' items');
        $this->outputDashedLine();

        if (count($items)) {
            $this->outputLine('GUID: ' . $items[0]->getGuid());
            $this->outputDashedLine();
            $this->outputLine(print_r($items[0]->toArray(), 1));
        }
    }

    /**
     * @param string $char
     */
    protected function outputDashedLine($char = '-')
    {
        $this->outputLine(str_repeat($char, self::MAXIMUM_LINE_LENGTH));
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
