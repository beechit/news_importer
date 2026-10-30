<?php

declare(strict_types=1);

namespace BeechIt\NewsImporter\Controller;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 12-06-2015
 * All code (c) Beech Applications B.V. all rights reserved
 */
use BeechIt\NewsImporter\Domain\Model\ImportSource;
use BeechIt\NewsImporter\Domain\Repository\ImportSourceRepository;
use BeechIt\NewsImporter\Exception\NewsItemNotFoundException;
use BeechIt\NewsImporter\Service\ExtractorService;
use BeechIt\NewsImporter\Service\ImportService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder as BackendUriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class AdminController
 */
class AdminController extends ActionController
{
    /**
     * The module name of this BE module
     */
    public const MODULE_NAME = 'web_NewsImporterNewsimporter';

    protected ImportSourceRepository $importSourceRepository;

    protected ExtractorService $extractorService;

    protected ImportService $importService;

    public function __construct(
        ImportSourceRepository $importSourceRepository,
        ExtractorService $extractorService,
        ImportService $importService,
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly IconFactory $iconFactory,
        private readonly BackendUriBuilder $backendUriBuilder
    ) {
        $this->importSourceRepository = $importSourceRepository;
        $this->extractorService = $extractorService;
        $this->importService = $importService;
    }

    protected function getErrorFlashMessage(): string|bool
    {
        return false;
    }

    protected function initializeLocale(ModuleTemplate $moduleTemplate): void
    {
        $backendUser = $this->getBackendUser();
        if ($backendUser !== null) {
            $userLang = (string)($backendUser->uc['lang'] ?? '');
            $lang = $userLang !== '' ? $userLang : 'en';
            $locale = $lang . '_' . strtoupper($lang);
            setlocale(
                LC_ALL,
                $lang,
                $locale,
                $locale . '.utf8',
                $userLang,
                (string)($GLOBALS['TYPO3_CONF_VARS']['SYS']['systemLocale'] ?? '')
            );
            $moduleTemplate->assign('locale', $locale);
        }
    }

    protected function initializeModuleTemplate(): ModuleTemplate
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setFlashMessageQueue($this->getFlashMessageQueue());
        $this->initializeLocale($moduleTemplate);
        $this->registerButtons($moduleTemplate);
        return $moduleTemplate;
    }

    /**
     * Add flash message (with auto translation handling title and body)
     *
     * @param array<int|string, mixed>|null $arguments
     */
    public function addTranslatedFlashMessage(
        string $messageBody,
        string $messageTitle = '',
        ContextualFeedbackSeverity $severity = ContextualFeedbackSeverity::OK,
        ?array $arguments = null,
        bool $storeInSession = true
    ): void {
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
     * @param array<int|string, mixed>|null $arguments
     */
    public function getTranslatedString(string $input, ?array $arguments = null): string
    {
        if ($input === '') {
            return '';
        }
        $translated = LocalizationUtility::translate($input, $this->request->getControllerExtensionName(), $arguments);
        return $translated ?: '';
    }

    protected function getBackendUser(): ?BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'] ?? null;
    }

    /**
     * Index action
     */
    public function indexAction(): ResponseInterface
    {
        $parsedBody = $this->request->getParsedBody();
        $pageId = (int)($this->request->getQueryParams()['id'] ?? (is_array($parsedBody) ? ($parsedBody['id'] ?? 0) : 0));
        $importSources = $this->importSourceRepository->findBy(['pid' => $pageId]);
        if ($importSources->count() === 0) {
            $this->addTranslatedFlashMessage('select-page-with-importsources', '', ContextualFeedbackSeverity::WARNING);
        }
        if ($importSources->count() === 1) {
            return $this->redirect('show', null, null, ['importSource' => $importSources->getFirst()]);
        }
        $moduleTemplate = $this->initializeModuleTemplate();
        $moduleTemplate->assign('importSources', $importSources);
        return $moduleTemplate->renderResponse('Admin/Index');
    }

    public function showAction(ImportSource $importSource): ResponseInterface
    {
        $moduleTemplate = $this->initializeModuleTemplate();

        $moduleTemplate->assign('importSource', $importSource);

        $this->extractorService->setSource($importSource->getUrl());
        $this->extractorService->setMapping($importSource->getMapping());
        $extractedItems = $this->extractorService->getItems();

        $storagePid = $importSource->getStoragePid() ?: (int)$importSource->getPid();
        $items = [];
        foreach ($extractedItems as $item) {
            $newsUid = false;
            try {
                $newsUid = $this->importService->getNewsItemUid($storagePid, $item->getGuid());
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

        $moduleTemplate->assign('items', $items);
        return $moduleTemplate->renderResponse('Admin/Show');
    }

    /**
     * @throws NewsItemNotFoundException
     */
    public function importAction(ImportSource $importSource, string $guid = ''): ResponseInterface
    {
        $this->extractorService->setSource($importSource->getUrl());
        $this->extractorService->setMapping($importSource->getMapping());
        $extractedItems = $this->extractorService->getItems();

        $storagePid = $importSource->getStoragePid() ?: (int)$importSource->getPid();
        foreach ($extractedItems as $item) {
            if ($item->getGuid() === $guid) {
                $this->importService->importItem($importSource, $item);
                $itemUid = $this->importService->getNewsItemUid($storagePid, $guid);
                $this->uriBuilder->reset()->setCreateAbsoluteUri(true);
                if ($this->request->getAttribute('normalizedParams')?->isHttps()) {
                    $this->uriBuilder->setAbsoluteUriScheme('https');
                }
                $uri = $this->backendUriBuilder->buildUriFromRoute('record_edit', [
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
                return $this->redirectToUri((string)$uri);
            }
        }

        $this->addTranslatedFlashMessage('requested-item-not-found', '', ContextualFeedbackSeverity::ERROR);
        return $this->redirect('show', null, null, ['importSource' => $importSource]);
    }

    /**
     * Create the panel of buttons for submitting the form or otherwise perform operations.
     */
    protected function registerButtons(?ModuleTemplate $moduleTemplate = null): void
    {
        $moduleTemplate ??= $this->moduleTemplateFactory->create($this->request);
        $buttonBar = $moduleTemplate->getDocHeaderComponent()->getButtonBar();

        $lang = $this->getLanguageService();

        // Refresh page
        $refreshLink = $this->request->getAttribute('normalizedParams')?->getRequestUri() ?? '';
        if ($refreshLink !== '') {
            $refreshButton = $buttonBar->makeLinkButton()
                ->setHref($refreshLink)
                ->setTitle($lang->sL('LLL:EXT:core/Resources/Private/Language/locallang_core.xlf:labels.reload'))
                ->setIcon($this->iconFactory->getIcon('actions-refresh', IconSize::SMALL));
            $buttonBar->addButton($refreshButton, ButtonBar::BUTTON_POSITION_RIGHT);
        }

        // Shortcut
        $backendUser = $this->getBackendUser();
        if ($backendUser !== null && $backendUser->mayMakeShortcut()) {
            $shortCutButton = $buttonBar->makeShortcutButton()
                ->setRouteIdentifier(self::MODULE_NAME)
                ->setDisplayName($lang->sL('LLL:EXT:news_importer/Resources/Private/Language/locallang_be_module.xlf:mlang_tabs_tab') ?: 'News importer')
                ->setArguments([
                    'id' => (int)($this->request->getQueryParams()['id'] ?? 0),
                ]);
            $buttonBar->addButton($shortCutButton, ButtonBar::BUTTON_POSITION_RIGHT);
        }
    }

    /**
     * Returns an instance of LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
