<?php

namespace BeechIt\NewsImporter\Command;

use BeechIt\NewsImporter\Domain\Model\ImportSource;
use BeechIt\NewsImporter\Domain\Repository\ImportSourceRepository;
use BeechIt\NewsImporter\Service\ExtractorService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestImportSourceCommand extends Command
{
    protected const ARGUMENT_IMPORT_SOURCE = 'importSource';

    /**
     * @var ExtractorService
     */
    protected ExtractorService $extractorService;

    /**
     * @var ImportSourceRepository
     */
    protected ImportSourceRepository $importSourceRepository;

    /**
     * @param ExtractorService $extractorService
     * @param ImportSourceRepository $importSourceRepository
     */
    public function __construct(
        ExtractorService $extractorService,
        ImportSourceRepository $importSourceRepository
    ) {
        parent::__construct();
        $this->extractorService = $extractorService;
        $this->importSourceRepository = $importSourceRepository;
    }

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure(): void
    {
        $this->setHelp($this->getDescription());
        $this->addArgument(
            self::ARGUMENT_IMPORT_SOURCE,
            InputArgument::REQUIRED,
            'The uid of the importSource you want to test'
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
        $importSourceUid = $input->getArgument(self::ARGUMENT_IMPORT_SOURCE);
        if (!is_numeric($importSourceUid)) {
            $this->io->error('Please provide a numeric uid for the argument ' . self::ARGUMENT_IMPORT_SOURCE);
            return Command::INVALID;
        }
        $importSource = $this->importSourceRepository->findByUid((int)$importSourceUid);
        if (!$importSource instanceof ImportSource) {
            $this->io->warning('No importSource found for given uid:' . $importSourceUid);
            return Command::FAILURE;
        }

        $this->io->info('Fetch: ' . $importSource->getUrl());
        $this->extractorService->setSource($importSource->getUrl());
        $this->extractorService->setMapping($importSource->getMapping());
        $items = $this->extractorService->getItems();

        $this->io->info('Found ' . count($items) . ' items');

        if (count($items)) {
            $messages = [];
            $messages[] = 'GUID: ' . $items[0]->getGuid();
            $messages[] = ' '; //empty line
            $messages[] = print_r($items[0]->toArray(), true);
            $this->io->info($messages);
        }

        return Command::SUCCESS;
    }
}
