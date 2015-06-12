<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'News importer');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_newsimporter_domain_model_importsource', 'EXT:news_importer/Resources/Private/Language/locallang_csh_tx_newsimporter_domain_model_importsource.xlf');

//add contains plugin
$TCA['pages']['columns']['module']['config']['items'][] = array('News import sources', 'imports', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'ext_icon.png');
\TYPO3\CMS\Backend\Sprite\SpriteManager::addTcaTypeIcon('pages', 'contains-imports', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'ext_icon.png');


if (TYPO3_MODE === 'BE') {

	/**
	 * Registers a Backend Module
	 */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'BeechIt.' . $_EXTKEY,
		'web',
		'newsimporter',
		'', // Position
		array(
			'Admin' => 'index,show,import',
		),
		array(
			'access' => 'user,group',
			'icon' => 'EXT:' . $_EXTKEY . '/ext_icon.png',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_be_module.xlf',
		)
	);
}