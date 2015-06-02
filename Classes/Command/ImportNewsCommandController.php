<?php
namespace BeechIt\NewsImporter\Command;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 12-05-2015 15:21
 * All code (c) Beech Applications B.V. all rights reserved
 */
use BeechIt\NewsImporter\Domain\Model\ImportSource;
use BeechIt\NewsImporter\Domain\Model\Remote;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Rsaauth\Storage\StorageFactory;

/**
 * Class ImportNewsCommand controller
 */
class ImportNewsCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
	 * @inject
	 */
	protected $configurationManager;

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
	 * Call command
	 */
	protected function callCommandMethod() {
		$this->settings = $this->configurationManager->getConfiguration(
			ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'newsImporter'
		);
		/** @var StorageRepository $storageRepository */
		$storageRepository = $this->objectManager->get(StorageRepository::class);
		foreach($storageRepository->findAll() as $storage) {
			$storage->setEvaluatePermissions(FALSE);
		}
		parent::callCommandMethod();
	}

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
		$this->outputLine('settings: ' . ($this->settings ? print_r($this->settings, 1) : 'NO TYPOSCRIPT SETTINGS'));
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
		$importReport = array();

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

					// clean body text
					if (!empty($data['bodytext'])) {
						$data['bodytext'] = $this->cleanBodyText($data['bodytext'], $data['pid']);
					}

					// parse media
					$data['media'] = $this->processMedia($data, $importSource);

					$this->newsImportService->import(array($data));

					$this->outputLine('Imported: ' . $item->getGuid());
					$importReport[] = $data['title'] . '; ' . $item->getGuid();
				} else {
					$this->outputLine('Already imported: ' . $item->getGuid());
				}
			}
			$importSource->setLastRun(new \DateTime());
			$this->importSourceRepository->update($importSource);
		}
		if ($importReport !== array() && !empty($this->settings['notification']['recipients'])) {
			/** @var MailMessage $message */
			$message = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Mail\\MailMessage');
			$message->setTo($this->settings['notification']['recipients'])
				->setSubject($this->settings['notification']['subject'] ?: 'New items imported');
			if ($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress']) {
				$message->setFrom($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'], $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] ?: NULL);
			}
			$message->setBody(
				vsprintf(
					$this->settings['notification']['body'] ?: 'Imported %1$d items: %2$s',
					array(
						count($importReport),
						PHP_EOL . PHP_EOL . implode(PHP_EOL, $importReport)
					)
				)
			);
			$message->send();
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
	 * Clean body text by RTE settings
	 *
	 * @param string $text
	 * @param int $pid
	 */
	protected function cleanBodyText($text, $pid) {
		static $rteHtmlParsers;

		if (!isset($rteHtmlParsers[$pid])) {
			if (!is_array($rteHtmlParsers)) {
				$rteHtmlParsers = array();
			}
			/** @var $htmlParser \TYPO3\CMS\Core\Html\RteHtmlParser */
			$rteHtmlParsers[$pid] = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Html\\RteHtmlParser');
			$rteHtmlParsers[$pid]->init('tx_news_domain_model_news:bodytext', $pid);
		}

		// Perform transformation
		$tsConfig = \TYPO3\CMS\Backend\Utility\BackendUtility::getPagesTSconfig($pid);
		return $rteHtmlParsers[$pid]->RTE_transform(trim($text), array('rte_transform' => array('parameters' => array('flag=rte_disabled','mode=ts_css'))), 'db', $tsConfig['RTE.']['default.']);
	}

	/**
	 * @param array $data
	 * @param ImportSource $importSource
	 * @return NULL|array
	 */
	protected function processMedia(array $data, ImportSource $importSource) {
		$media = NULL;
		if (empty($data['image']) && $importSource->getDefaultImage()) {
			return array(
				array(
					'type' => 0,
					'image' => $importSource->getDefaultImage()->getOriginalResource()->getCombinedIdentifier(),
					'showinpreview' => 1
				)
			);
		}

		$folder = NULL;
		if ($importSource->getImageFolder()) {
			try {
				$folder = ResourceFactory::getInstance()->getFolderObjectFromCombinedIdentifier(ltrim($importSource->getImageFolder(), 'file:'));
			} catch (\Exception $e) {}
		}

		if (!empty($data['image']) && $folder) {
			$tmp = GeneralUtility::getUrl($data['image']);
			if ($tmp) {
				$tempFile = GeneralUtility::tempnam('news_importer');
				file_put_contents($tempFile, $tmp);
				list(,,$imageType) = getimagesize($tempFile);
				try {
					$image = $folder->addFile($tempFile, ($data['title'] ?: 'news_import') . image_type_to_extension($imageType, TRUE), 'changeName');
					$media = array(
						array(
							'type' => 0,
							'image' => $image->getCombinedIdentifier(),
							'showinpreview' => 1
						)
					);
				} catch (\Exception $e) {}
			}
		}
		return $media;
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