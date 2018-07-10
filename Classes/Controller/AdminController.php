<?php
namespace BeechIt\NewsImporter\Controller;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 12-06-2015
 * All code (c) Beech Applications B.V. all rights reserved
 */
use BeechIt\NewsImporter\Domain\Model\ExtractedItem;
use BeechIt\NewsImporter\Domain\Model\ImportSource;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;


/**
 * Class AdminController
 */
class AdminController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

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
	 * @var \BeechIt\NewsImporter\Service\ImportService
	 * @inject
	 */
	protected $importService;

	/**
	 * @return bool|string
	 */
	protected function getErrorFlashMessage() {
		return FALSE;
	}

	/**
	 * initialize view
	 */
	public function initializeView(ViewInterface $view) {
		parent::initializeView($view);
		if ($this->getBackendUser()) {
			$lang = $this->getBackendUser()->uc['lang'] ?: 'en';
			$locale = $lang . '_' . strtoupper($lang);
			setlocale(LC_ALL, $lang, $locale, $locale . '.utf8', $this->getBackendUser()->uc['lang'], $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLocale']);
			$view->assign('locale', $locale);
		}
	}

    /**
     * Add flash message (with auto translation handling title and body)
     *
     * @param $messageBody
     * @param string $messageTitle
     * @param int $severity
     * @param array|null $arguments
     * @param bool $storeInSession
     */
    public function addTranslatedFlashMessage(
        $messageBody,
        $messageTitle = '',
        $severity = AbstractMessage::OK,
        array $arguments = null,
        $storeInSession = true
    ) {
        $this->addFlashMessage($this->getTranslatedString($messageBody, $arguments),
            $this->getTranslatedString($messageTitle, $arguments),
            $severity,
            $storeInSession
        );
    }

    /**
     * Translate input string with additional arguments
     *
     * @param $input
     * @param $arguments
     * @return string
     */
    public function getTranslatedString($input, $arguments): string
    {
        if (!$input) {
            return '';
        }
        $translated = LocalizationUtility::translate($input, $this->extensionName, $arguments);
        return $translated ?: '';
    }

	/**
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}

	/**
	 * Index action
	 */
	public function indexAction() {
		$importSources = $this->importSourceRepository->findByPid((int)$_GET['id']);
		if ($importSources->count() === 0) {
			$this->addTranslatedFlashMessage('select-page-with-importsources', '', AbstractMessage::WARNING);
		}
		if ($importSources->count() === 1) {
			$this->redirect('show', NULL, NULL, ['importSource' => $importSources->getFirst()]);
		}
		$this->view->assign('importSources', $importSources);
	}

	/**
	 * @param ImportSource $importSource
	 */
	public function showAction(ImportSource $importSource) {
		$this->view->assign('importSource', $importSource);

		$this->extractorService->setSource($importSource->getUrl());
		$this->extractorService->setMapping($importSource->getMapping());
		$extractedItems = $this->extractorService->getItems();

		$items = [];
		/** @var ExtractedItem $item */
		foreach ($extractedItems as $item) {
			$items[] = [
				'guid' => $item->getGuid(),
				'title' => $item->extractValue('title'),
				'link' => $item->extractValue('link'),
				'datetime' => $item->extractValue('datetime'),
				'newsUid' => $this->importService->alreadyImported($importSource->getPid(), $item->getGuid())
            ];
		}

		$this->view->assign('items', $items);
	}

    /**
     * @param ImportSource $importSource
     * @param string $guid
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     */
	public function importAction(ImportSource $importSource, $guid) {
		$this->extractorService->setSource($importSource->getUrl());
		$this->extractorService->setMapping($importSource->getMapping());
		$extractedItems = $this->extractorService->getItems();

		foreach ($extractedItems as $item) {
			if ($item->getGuid() === $guid) {
				$this->importService->importItem($importSource, $item);
				$itemUid = $this->importService->alreadyImported($importSource->getPid(), $guid);

				$this->uriBuilder->reset()->setCreateAbsoluteUri(TRUE);
				if (\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SSL')) {
					$this->uriBuilder->setAbsoluteUriScheme('https');
				}
				$returnUrl = $this->uriBuilder->uriFor('show', ['importSource' => $importSource], $this->request->getControllerName());
				$this->redirectToUri('alt_doc.php?returnUrl=' . rawurlencode($returnUrl) . '&edit[tx_news_domain_model_news][' . $itemUid . ']=edit&disHelp=1');
			}
		}

		$this->addTranslatedFlashMessage('requested-item-not-found', '', AbstractMessage::ERROR);
		$this->redirect('show', NULL, NULL, ['importSource' => $importSource]);
	}
}