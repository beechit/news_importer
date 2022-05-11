<?php

namespace BeechIt\NewsImporter\Command;

use BeechIt\NewsImporter\Domain\Model\ImportSource;
use BeechIt\NewsImporter\Domain\Repository\ImportSourceRepository;
use BeechIt\NewsImporter\Service\ExtractorService;
use BeechIt\NewsImporter\Service\ImportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class ImportNewsCommand extends Command
{
    protected const OPTION_NAME_LIMIT = 'limit';
    protected const OPTION_NAME_LIMIT_SHORTCUT = 'l';
    protected const OPTION_LIMIT_DEFAULT = 1;
    /**
     * @var array
     */
    protected $settings;

    /**
     * @var ConfigurationManagerInterface
     */
    protected ConfigurationManagerInterface $configurationManager;

    /**
     * @var PersistenceManager
     */
    protected PersistenceManager $persistenceManager;

    /**
     * @var ImportSourceRepository
     */
    protected ImportSourceRepository $importSourceRepository;

    /**
     * @var ExtractorService
     */
    protected ExtractorService $extractorService;

    /**
     * @var ImportService
     */
    protected ImportService $importService;
    protected SymfonyStyle $io;

    public function __construct(
        ConfigurationManagerInterface $configurationManager,
        PersistenceManager $persistenceManager,
        ImportSourceRepository $importSourceRepository,
        ExtractorService $extractorService,
        ImportService $importService,
        StorageRepository $storageRepository
    ) {
        parent::__construct();
        $this->configurationManager = $configurationManager;
        $this->persistenceManager = $persistenceManager;
        $this->importSourceRepository = $importSourceRepository;
        $this->extractorService = $extractorService;
        $this->importService = $importService;
        $this->settings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'newsImporter'
        );
        $this->setEvaluatePermissionsOnFalse($storageRepository);
    }

    /**
     * @param StorageRepository $storageRepository
     * @return void
     */
    protected function setEvaluatePermissionsOnFalse(StorageRepository $storageRepository): void
    {
        foreach ($storageRepository->findAll() as $storage) {
            $storage->setEvaluatePermissions(false);
        }
    }

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure(): void
    {
        $this->setHelp($this->getDescription());
        $this->addOption(
            self::OPTION_NAME_LIMIT,
            self::OPTION_NAME_LIMIT_SHORTCUT,
            InputOption::VALUE_OPTIONAL,
            'Limit of importSource to import at this run',
            self::OPTION_LIMIT_DEFAULT
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title($this->getDescription());
        $limit = $input->getOption(self::OPTION_NAME_LIMIT);
        if (!is_numeric($limit)) {
            $this->io->warning('Please provide an number for the limit:' . $limit);
            return Command::INVALID;
        }

        $importSources = $this->importSourceRepository->findSourcesToImport((int)$limit);
        $importReport = [];
        $searchFields = $this->getSearchFields();

        /** @var ImportSource $importSource */
        foreach ($importSources as $importSource) {
            $this->io->writeln($importSource->getTitle());
            $this->io->newLine();
            $this->extractorService->setSource($importSource->getUrl());
            $this->extractorService->setMapping($importSource->getMapping());
            $items = $this->extractorService->getItems();

            $this->io->progressStart(count($items));
            foreach ($items as $item) {
                $this->io->progressAdvance();
                if ($this->importService->alreadyImported($importSource->getStoragePid(), $item->getGuid())) {
                    $this->io->writeln('Already imported: ' . $item->getGuid());
                } elseif ($importSource->getFilterWords() && !$this->importService->matchFilter(
                        $item,
                        $importSource->getFilterWords(),
                        $searchFields
                    )) {
                    $this->io->writeln('Skipped: ' . $item->getGuid() . '; Filter mismatch');
                } else {
                    $this->importService->importItem($importSource, $item);
                    $this->io->writeln('Imported: ' . $item->getGuid());
                    $importReport[] = $item->extractValue('title') . '; ' . $item->getGuid();
                }
            }
            $this->io->progressFinish();

            if (!$items) {
                $this->io->writeln('No items found');
            }
            $importSource->setLastRun(new \DateTime());
            $this->importSourceRepository->update($importSource);
            $this->persistenceManager->persistAll();
        }
        $this->sendImportReport($importReport);
        return Command::SUCCESS;
    }

    /**
     * @return string[]
     */
    protected function getSearchFields(): array
    {
        $searchFields = ['title', 'bodytext'];
        if (isset($this->settings['filter']['searchFields']) && is_array($this->settings['filter']['searchFields'])) {
            $searchFields = $this->settings['filter']['searchFields'];
        }
        return $searchFields;
    }

    /**
     * @param array $importReport
     */
    protected function sendImportReport(array $importReport): void
    {
        if ($importReport === []) {
            return;
        }
        if (empty($this->settings['notification']['recipients'])) {
            return;
        }
        /** @var MailMessage $message */
        $message = GeneralUtility::makeInstance(MailMessage::class);
        $message->setTo($this->settings['notification']['recipients'])
            ->setSubject($this->settings['notification']['subject'] ?: 'New items imported');
        if ($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress']) {
            $message->setFrom(
                $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'],
                $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] ?: null
            );
        }
        $message->text(
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
