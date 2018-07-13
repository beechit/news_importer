<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(
    function ($packageKey) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($packageKey, 'Configuration/TypoScript',
            'News importer');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_newsimporter_domain_model_importsource',
            'EXT:news_importer/Resources/Private/Language/locallang_csh_tx_newsimporter_domain_model_importsource.xlf');

        // Register icons
        /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Imaging\IconRegistry::class
        );
        $iconRegistry->registerIcon(
            'apps-pagetree-folder-contains-imports',
            \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
            ['source' => 'EXT:' . $packageKey . '/ext_icon.png']
        );

        if (TYPO3_MODE === 'BE') {

            /**
             * Registers a Backend Module
             */
            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'BeechIt.' . $packageKey,
                'web',
                'newsimporter',
                '', // Position
                [
                    'Admin' => 'index,show,import',
                ],
                [
                    'access' => 'user,group',
                    'icon' => 'EXT:' . $packageKey . '/ext_icon.png',
                    'labels' => 'LLL:EXT:' . $packageKey . '/Resources/Private/Language/locallang_be_module.xlf',
                ]
            );
        }
    },
    $_EXTKEY
);