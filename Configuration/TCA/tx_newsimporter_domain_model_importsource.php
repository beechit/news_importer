<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource',
        'label' => 'title',
        'label_alt' => 'storage_pid, url',
        'label_alt_force' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,

        'versioningWS' => 2,
        'versioning_followPages' => true,

        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],

        'requestUpdate' => 'disable_auto_import',

        'searchFields' => 'title,url,mapping,filter',
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('news_importer') . 'ext_icon.png'
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, title, url, mapping, last_run, storage_pid, default_image, image_folder, filter, update_interval, disable_auto_import'
    ],
    'types' => [
        '1' => [
            'showitem' => '
			title,
			url,
			mapping,
			storage_pid,
			default_image,
			image_folder,
			--palette--;LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:palette.automation;cron,
			--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access,sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, starttime, endtime'
        ],
    ],
    'palettes' => [
        'cron' => [
            'showitem' => 'filter, --linebreak--, last_run, update_interval,disable_auto_import',
            'canNotCollapse' => true
        ],
    ],
    'columns' => [

        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => [
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1],
                    ['LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0]
                ],
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_newsimporter_domain_model_importsource',
                'foreign_table_where' => 'AND tx_newsimporter_domain_model_importsource.pid=###CURRENT_PID### AND tx_newsimporter_domain_model_importsource.sys_language_uid IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],

        't3ver_label' => [
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ]
        ],

        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'starttime' => [
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ],
            ],
        ],
        'endtime' => [
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ],
            ],
        ],

        'title' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.title',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim'
            ]
        ],
        'url' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.url',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            ],
        ],
        'mapping' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.mapping',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim',
                'default' => '
items = item
item {
	guid = guid
	title = title
	externalurl = link
	type {
		defaultValue = 2
	}
	bodytext = description
	datetime {
		selector = pubDate
		strtotime = 1
	}
	image {
		selector = enclosure
		attr = url
	}
}
				'
            ]
        ],
        'disable_auto_import' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.disable_auto_import',
            'config' => [
                'type' => 'check',
            ],
        ],
        'last_run' => [
            'exclude' => 1,
            'displayCond' => 'FIELD:disable_auto_import:REQ:false',
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.last_run',
            'config' => [
                'type' => 'input',
                'size' => 10,
                'eval' => 'datetime',
                'readOnly' => 1
            ],
        ],
        'storage_pid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.storage_pid',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
                'show_thumbs' => 1,
                'wizards' => [
                    'suggest' => [
                        'type' => 'suggest',
                    ],
                ],
            ]
        ],
        'default_image' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.default_image',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'default_image',
                [
                    'maxitems' => 1,
                    'appearance' => [
                        'createNewRelationLinkTitle' => 'LLL:EXT:cms/locallang_ttc.xlf:images.addFileReference'
                    ],
                    'foreign_types' => [
                        '0' => [
                            'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => [
                            'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => [
                            'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => [
                            'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => [
                            'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ],
                        \TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => [
                            'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
                        ]
                    ],
                    // foreing_match is needed for FE upload purposes
                    'foreign_match_fields' => [
                        'fieldname' => 'default_image',
                        'tablenames' => 'tx_newsimporter_domain_model_importsource',
                        'table_local' => 'sys_file',
                    ],
                ], $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            ),
        ],
        'image_folder' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.image_folder',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'wizards' => [
                    '_PADDING' => 2,
                    'link' => [
                        'type' => 'popup',
                        'title' => 'LLL:EXT:cms/locallang_ttc.xlf:image_link_formlabel',
                        'icon' => 'link_popup.gif',
                        'module' => [
                            'name' => 'wizard_element_browser',
                            'urlParameters' => [
                                'mode' => 'wizard'
                            ]
                        ],
                        'params' => [
                            'blindLinkOptions' => 'page,file,mail,spec,url',
                            'blindLinkFields' => 'target,title,class,params'
                        ],
                        'JSopenParams' => 'height=800,width=600,status=0,menubar=0,scrollbars=1'
                    ]
                ],
            ],
        ],
        'filter' => [
            'exclude' => 1,
            'displayCond' => 'FIELD:disable_auto_import:REQ:false',
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.filter',
            'config' => [
                'type' => 'input',
                'size' => '50',
                'eval' => 'trim'
            ]
        ],

        'update_interval' => [
            'exclude' => 1,
            'displayCond' => 'FIELD:disable_auto_import:REQ:false',
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.update_interval',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'eval' => 'timesec',
                'default' => '7200'
            ]
        ],
    ],
];
