<?php

declare(strict_types=1);

namespace BeechIt\NewsImporter\Command;

use BeechIt\NewsImporter\Domain\Model\ImportSource;
use BeechIt\NewsImporter\Domain\Repository\ImportSourceRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class OutputNewsImportStatusesCommand extends Command
{
    protected ImportSourceRepository $importSourceRepository;

    /**
     * @var array<string, mixed>
     */
    protected array $settings = [];

    protected ConfigurationManagerInterface $configurationManager;

    protected SymfonyStyle $io;

    public function __construct(
        ImportSourceRepository $importSourceRepository,
        ConfigurationManagerInterface $configurationManager
    ) {
        $this->importSourceRepository = $importSourceRepository;
        $this->configurationManager = $configurationManager;
        parent::__construct();
    }

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure(): void
    {
        $this->setHelp($this->getDescription());
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->settings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'newsImporter'
        );
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title($this->getDescription());
        $remotes = $this->importSourceRepository->findAll();
        $remoteMessages = [];
        /** @var ImportSource $remote */
        foreach ($remotes as $remote) {
            $lastRun = '';
            if ($remote->getDisableAutoImport()) {
                $lastRun = ' - auto import is disabled!';
            } elseif ($remote->getLastRun() !== null) {
                $lastRun = ' - last run: ' . $remote->getLastRun()->format('Y-m-d H:i:s');
            }
            $remoteMessages[] = '[' . $remote->getUid() . '] ' . $remote->getUrl() . $lastRun;
        }
        if ($remoteMessages !== []) {
            $this->io->info($remoteMessages);
        }
        if ($remotes->count() === 0) {
            $this->io->warning('No remotes found!');
        }

        $this->io->info('settings: ' . ($this->settings ? print_r($this->settings, true) : 'NO TYPOSCRIPT SETTINGS'));
        return Command::SUCCESS;
    }
}
