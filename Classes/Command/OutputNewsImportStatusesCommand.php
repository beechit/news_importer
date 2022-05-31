<?php

namespace BeechIt\NewsImporter\Command;

use BeechIt\NewsImporter\Domain\Model\ImportSource;
use BeechIt\NewsImporter\Domain\Repository\ImportSourceRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

class OutputNewsImportStatusesCommand extends Command
{
    /**
     * @var ImportSourceRepository
     */
    protected ImportSourceRepository $importSourceRepository;

    /**
     * @var array
     */
    protected array $settings = [];

    public function __construct(
        ImportSourceRepository $importSourceRepository,
        ConfigurationManager $configurationManager
    ) {
        $this->importSourceRepository = $importSourceRepository;
        $this->settings = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'newsImporter'
        );
        parent::__construct();
    }

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure()
    {
        $this->setHelp($this->getDescription());
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
        $remotes = $this->importSourceRepository->findAll();
        $remoteMessages = [];
        /** @var ImportSource $remote */
        foreach ($remotes as $remote) {
            $lastRun = '';
            if ($remote->getDisableAutoImport()) {
                $lastRun = ' - auto import is disabled!';
            } elseif ($remote->getLastRun()) {
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

        $this->io->info('settings: ' . ($this->settings ? print_r($this->settings, 1) : 'NO TYPOSCRIPT SETTINGS'));
        return Command::SUCCESS;
    }
}
