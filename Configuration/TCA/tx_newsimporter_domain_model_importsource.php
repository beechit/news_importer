<?php

return array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource',
		'label' => 'url',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,

		'versioningWS' => 2,
		'versioning_followPages' => TRUE,

		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),
		'searchFields' => 'url,mapping,last_run,storage_pid,image_folder,',
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('news_importer') . 'ext_icon.png'
	),
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, url, mapping, last_run, storage_pid', //todo: implement: image_folder
	),
	'types' => array(
		'1' => array('showitem' => 'url, mapping, storage_pid, last_run, --div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access,sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, starttime, endtime'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(

		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0)
				),
			),
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_newsimporter_domain_model_importsource',
				'foreign_table_where' => 'AND tx_newsimporter_domain_model_importsource.pid=###CURRENT_PID### AND tx_newsimporter_domain_model_importsource.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),

		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			)
		),

		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),

		'url' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.url',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim'
			),
		),
		'mapping' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.mapping',
			'config' => array(
				'type' => 'text',
				'cols' => 40,
				'rows' => 15,
				'eval' => 'trim',
				'default' => '
items = item
item {
	title = title
	link = link
	bodytext = description
	datetime {
		selector = pubDate
		strtotime = 1
	}
}
				'
			)
		),
		'last_run' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.last_run',
			'config' => array(
				'type' => 'input',
				'size' => 10,
				'eval' => 'datetime',
				'readOnly' => 1
			),
		),
		'storage_pid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.storage_pid',
			'config' => array(
				'type' => 'group',
				'internal_type' => 'db',
				'allowed' => 'pages',
				'size' => 1,
				'maxitems' => 1,
				'minitems' => 0,
				'show_thumbs' => 1,
				'wizards' => array(
					'suggest' => array(
						'type' => 'suggest',
					),
				),
			)
		),
		'image_folder' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.image_folder',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'eval' => 'trim',
				'wizards' => array(
					'_PADDING' => 2,
					'link' => array(
						'type' => 'popup',
						'title' => 'LLL:EXT:cms/locallang_ttc.xlf:image_link_formlabel',
						'icon' => 'link_popup.gif',
						'module' => array(
							'name' => 'wizard_element_browser',
							'urlParameters' => array(
								'mode' => 'wizard'
							)
						),
						'params' => array(
							'blindLinkOptions' => 'page,file,mail,spec,url',
							'blindLinkFields' => 'target,title,class,params'
						),
						'JSopenParams' => 'height=800,width=600,status=0,menubar=0,scrollbars=1'
					)
				),
			),
		),

	),
);
