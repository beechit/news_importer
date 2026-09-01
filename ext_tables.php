<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(
    function ($packageKey) {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            $packageKey,
            'Configuration/TypoScript',
            'News importer'
        );

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
    },
    'news_importer'
);
