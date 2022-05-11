<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$pathQueryLib = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('news_importer') .
    'Resources/Private/PHP/querypath/src/qp.php';
// Add custom autoloader for QueryPath

if (file_exists($pathQueryLib)) {
    require_once $pathQueryLib;
}
