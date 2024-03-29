<?php

namespace BeechIt\NewsImporter\Controller;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 12-06-2015
 * All code (c) Beech Applications B.V. all rights reserved
 */
use BeechIt\NewsImporter\Domain\Model\ExtractedItem;
use BeechIt\NewsImporter\Domain\Model\ImportSource;
use BeechIt\NewsImporter\Domain\Repository\ImportSourceRepository;
use BeechIt\NewsImporter\Exception\NewsItemNotFoundException;
use BeechIt\NewsImporter\Service\ExtractorService;
use BeechIt\NewsImporter\Service\ImportService;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class AdminController
 */
class AdminController extends ActionController
{

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

    /**
     * @var BackendTemplateView
     */
    protected $view;

    /**
     * BackendTemplateView Container
     *
     * @var BackendTemplateView
     */
    protected $defaultViewObjectName = BackendTemplateView::class;

    /**
     * The module name of this BE module
     */
    const MODULE_NAME = 'web_NewsImporterNewsimporter';
    public function __construct(ImportSourceRepository $importSourceRepository, ExtractorService $extractorService, ImportService $importService)
    {
        $this->importSourceRepository = $importSourceRepository;
        $this->extractorService = $extractorService;
        $this->importService = $importService;
    }

    /**
     * @return bool|string
     */
    protected function getErrorFlashMessage()
    {
        return false;
    }

    /**
     * initialize view
     */
    public function initializeView(ViewInterface $view)
    {
        /** @var BackendTemplateView $view */
        parent::initializeView($view);
        if ($this->getBackendUser()) {
            $lang = $this->getBackendUser()->uc['lang'] ?: 'en';
            $locale = $lang . '_' . strtoupper($lang);
            setlocale(
                LC_ALL,
                $lang,
                $locale,
                $locale . '.utf8',
                $this->getBackendUser()->uc['lang'],
                $GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLocale']
            );
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
        $this->addFlashMessage(
            $this->getTranslatedString($messageBody, $arguments),
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
        $translated = LocalizationUtility::translate($input, $this->request->getControllerExtensionName(), $arguments);
        return $translated ?: '';
    }

    /**
     * @return BackendUserAuthentication
     */
    protected function getBackendUser()
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $importSources = $this->importSourceRepository->findByPid((int)$_GET['id']);
        if ($importSources->count() === 0) {
            $this->addTranslatedFlashMessage('select-page-with-importsources', '', AbstractMessage::WARNING);
        }
        if ($importSources->count() === 1) {
            $this->redirect('show', null, null, ['importSource' => $importSources->getFirst()]);
        }
        $this->view->assign('importSources', $importSources);
    }

    /**
     * @param ImportSource $importSource
     */
    public function showAction(ImportSource $importSource)
    {
        $this->registerButtons();

        $this->view->assign('importSource', $importSource);

        $this->extractorService->setSource($importSource->getUrl());
        $this->extractorService->setMapping($importSource->getMapping());
        $extractedItems = $this->extractorService->getItems();

        $items = [];
        /** @var ExtractedItem $item */
        foreach ($extractedItems as $item) {
            $newsUid = false;
            try {
                $newsUid = $this->importService->getNewsItemUid($importSource->getPid(), $item->getGuid());
            } catch (NewsItemNotFoundException $e) {
            }
            $items[] = [
                'guid' => $item->getGuid(),
                'title' => $item->extractValue('title'),
                'link' => $item->extractValue('link'),
                'datetime' => $item->extractValue('datetime'),
                'newsUid' => $newsUid,
            ];
        }

        $this->view->assign('items', $items);
    }

    /**
     * @param ImportSource $importSource
     * @param string $guid
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
     * @throws \BeechIt\NewsImporter\Exception\NewsItemNotFoundException
     */
    public function importAction(ImportSource $importSource, $guid)
    {
        $this->extractorService->setSource($importSource->getUrl());
        $this->extractorService->setMapping($importSource->getMapping());
        $extractedItems = $this->extractorService->getItems();

        foreach ($extractedItems as $item) {
            if ($item->getGuid() === $guid) {
                $this->importService->importItem($importSource, $item);
                $itemUid = $this->importService->getNewsItemUid($importSource->getPid(), $guid);
                $this->uriBuilder->reset()->setCreateAbsoluteUri(true);
                if (GeneralUtility::getIndpEnv('TYPO3_SSL')) {
                    $this->uriBuilder->setAbsoluteUriScheme('https');
                }
                $uri = GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('record_edit', [
                    'edit' => [
                        'tx_news_domain_model_news' => [
                            $itemUid => 'edit',
                        ],
                    ],
                    'returnUrl' => $this->uriBuilder->uriFor(
                        'show',
                        ['importSource' => $importSource],
                        $this->request->getControllerName()
                    ),
                ]);
                $this->redirectToUri($uri);
            }
        }

        $this->addTranslatedFlashMessage('requested-item-not-found', '', AbstractMessage::ERROR);
        $this->redirect('show', null, null, ['importSource' => $importSource]);
    }

    /**
     * Create the panel of buttons for submitting the form or otherwise perform operations.
     *
     * @return array All available buttons as an assoc. array
     */
    protected function registerButtons()
    {
        /** @var ButtonBar $buttonBar */
        $buttonBar = $this->view->getModuleTemplate()->getDocHeaderComponent()->getButtonBar();

        /** @var IconFactory $iconFactory */
        $iconFactory = $this->view->getModuleTemplate()->getIconFactory();

        $lang = $this->getLanguageService();

        // Refresh page
        $refreshLink = GeneralUtility::linkThisScript(
            [
                'target' => rawurlencode('#'),
            ]
        );
        $refreshButton = $buttonBar->makeLinkButton()
            ->setHref($refreshLink)
            ->setTitle($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.reload'))
            ->setIcon($iconFactory->getIcon('actions-refresh', Icon::SIZE_SMALL));
        $buttonBar->addButton($refreshButton, ButtonBar::BUTTON_POSITION_RIGHT);

        // Shortcut
        if ($this->getBackendUser()->mayMakeShortcut()) {
            $shortCutButton = $buttonBar->makeShortcutButton()->setModuleName(self::MODULE_NAME);
            $buttonBar->addButton($shortCutButton, ButtonBar::BUTTON_POSITION_RIGHT);
        }
    }

    /**
     * Returns an instance of LanguageService
     *
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
