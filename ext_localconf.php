<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$pathQueryLib = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) .
    'Resources/Private/PHP/querypath/src/qp.php';
// Add custom autoloader for QueryPath

if (file_exists($pathQueryLib)) {
    require_once $pathQueryLib;
}

if (TYPO3_MODE === 'BE') {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['extbase']['commandControllers'][]
        = 'BeechIt\\NewsImporter\\Command\\ImportNewsCommandController';
}
