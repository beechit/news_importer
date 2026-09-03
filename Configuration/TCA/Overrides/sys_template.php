<?php
defined('TYPO3') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'news_importer',
    'Configuration/TypoScript',
    'News importer'
);
