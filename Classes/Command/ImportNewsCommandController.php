<?php
namespace BeechIt\NewsImporter\Command;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 12-05-2015 15:21
 * All code (c) Beech Applications B.V. all rights reserved
 */
use BeechIt\NewsImporter\Domain\Model\ImportSource;
use BeechIt\NewsImporter\Domain\Model\Remote;

/**
 * Class ImportNewsCommand controller
 */
class ImportNewsCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

	/**
	 * @var \BeechIt\NewsImporter\Domain\Repository\ImportSourceRepository
	 * @inject
	 */
	protected $importSourceRepository;

	/**
	 * @var \BeechIt\NewsImporter\Service\ExtractorService
	 * @inject
	 */
	protected $extractorService;

	/**
	 * @var \Tx_News_Domain_Service_NewsImportService
	 * @inject
	 */
	protected $newsImportService;

	/**
	 * Get status of all defined remotes (last run datetime)
	 */
	public function statusCommand() {
		$this->outputDashedLine();
		$remotes = $this->importSourceRepository->findAll();
		/** @var Remote $remote */
		foreach ($remotes as $remote) {
			$this->outputLine($remote->getUrl());
		}
		if ($remotes->count() === 0) {
			$this->outputLine('No remotes found!');
		}
		$this->outputDashedLine();
	}

	/**
	 * Run importer
	 *
	 * @param int $limit number of sources to check
	 */
	public function runCommand($limit = 1) {
		// todo: implement $limit
		$importSources = $this->importSourceRepository->findAll();
		/** @var ImportSource $importSource */
		foreach ($importSources as $importSource) {
			$this->extractorService->setSource($importSource->getUrl());
			$this->extractorService->setMapping($importSource->getMapping());
			foreach ($this->extractorService->getItems() as $item) {
				if (!$this->alreadyImported($importSource->getStoragePid(), $item->getGuid())) {

					$data = $item->toArray();
					$data['pid'] = $importSource->getStoragePid();
					$data['import_id'] = $item->getGuid();
					$data['import_source'] = 'ext:news_importer';

					$this->newsImportService->import(array($data));

					$this->outputLine('Imported: ' . $item->getGuid());
				} else {
					$this->outputLine('Already imported: ' . $item->getGuid());
				}
			}
			$importSource->setLastRun(new \DateTime());
			$this->importSourceRepository->update($importSource);
		}
	}

	/**
	 * Check if news item already exists
	 *
	 * @param int $pid
	 * @param string $guid
	 * @return bool
	 */
	protected function alreadyImported($pid, $guid) {
		$guid =  $this->getDatabaseConnection()->fullQuoteStr($guid, 'tx_news_domain_model_news');
		$record = $this->getDatabaseConnection()->exec_SELECTgetSingleRow(
			'uid',
			'tx_news_domain_model_news',
			'deleted=0 AND pid=' . (int)$pid . ' AND import_source = \'ext:news_importer\' AND import_id=' . $guid
		);
		return $record ? TRUE : FALSE;
	}

	/**
	 * @param string $char
	 */
	protected function outputDashedLine($char = '-') {
		$this->outputLine(str_repeat($char, self::MAXIMUM_LINE_LENGTH));
	}

	/**
	 * @return \TYPO3\CMS\Core\Database\DatabaseConnection
	 */
	protected function getDatabaseConnection() {
		return $GLOBALS['TYPO3_DB'];
	}
}