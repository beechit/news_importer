<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource',
        'label' => 'title',
        'label_alt' => 'storage_pid, url',
        'label_alt_force' => true,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',

        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],

        'searchFields' => 'title,url,mapping,filter',
        'iconfile' => 'EXT:news_importer/ext_icon.png',
    ],
    'types' => [
        '1' => [
            'showitem' => 'title,url,mapping,storage_pid,default_image,image_folder,--palette--;LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:palette.automation;cron,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,sys_language_uid,--palette--,l10n_parent,l10n_diffsource,hidden,--palette--;;1,starttime,endtime',
        ],
    ],
    'palettes' => [
        'cron' => [
            'showitem' => 'filter, --linebreak--, last_run, update_interval,disable_auto_import',
            'canNotCollapse' => true,
        ],
    ],
    'columns' => [

        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => ['type' => 'language'],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['label' => '', 'value' => 0],
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
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ],
        ],

        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'starttime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'datetime',
                'size' => 13,
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
                ],
                ['behaviour' => ['allowLanguageSynchronization' => true]],
            ],
        ],
        'endtime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'datetime',
                'size' => 13,
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
                ],
                ['behaviour' => ['allowLanguageSynchronization' => true]],
            ],
        ],

        'title' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.title',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'eval' => 'trim',
            ],
        ],
        'url' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.url',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
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
				',
            ],
        ],
        'disable_auto_import' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.disable_auto_import',
            'config' => [
                'type' => 'check',
            ],
            'onChange' => 'reload',
        ],
        'last_run' => [
            'exclude' => 1,
            'displayCond' => 'FIELD:disable_auto_import:REQ:false',
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.last_run',
            'config' => [
                'type' => 'datetime',
                'size' => 10,
                'readOnly' => 1,
            ],
        ],
        'storage_pid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.storage_pid',
            'config' => [
                'type' => 'group',
                'allowed' => 'pages',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
            ],
        ],
        'default_image' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.default_image',
            'config' => [
                // TODO: Important! Verify that the fieldname value in foreign table either matches the column name
                // or is set properly in the following TCA, see https://docs.typo3.org/permalink/t3tca:confval-inline-foreign-match-fields
                'type' => 'file',
                'allowed' => $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext'],
                'maxitems' => 1,
                'appearance' => [
                    'createNewRelationLinkTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:images.addFileReference',
                ],
                // foreing_match is needed for FE upload purposes
                'foreign_match_fields' => [
                    'fieldname' => 'default_image',
                    'tablenames' => 'tx_newsimporter_domain_model_importsource',
                ],
                'overrideChildTca' => ['types' => [
                    '0' => [
                        'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette',
                    ],
                    \TYPO3\CMS\Core\Resource\FileType::TEXT->value => [
                        'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette',
                    ],
                    \TYPO3\CMS\Core\Resource\FileType::IMAGE->value => [
                        'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette',
                    ],
                    \TYPO3\CMS\Core\Resource\FileType::AUDIO->value => [
                        'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette',
                    ],
                    \TYPO3\CMS\Core\Resource\FileType::VIDEO->value => [
                        'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette',
                    ],
                    \TYPO3\CMS\Core\Resource\FileType::APPLICATION->value => [
                        'showitem' => '
							--palette--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette',
                    ],
                ]],
            ],
        ],
        'image_folder' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.image_folder',
            'config' => [
                'type' => 'link',
                'size' => 30,
                'allowedTypes' => ['folder', 'record', 'telephone'],
                'appearance' => ['browserTitle' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:image_link_formlabel', 'allowedOptions' => ['rel']],
            ],
        ],
        'filter' => [
            'exclude' => 1,
            'displayCond' => 'FIELD:disable_auto_import:REQ:false',
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.filter',
            'config' => [
                'type' => 'input',
                'size' => '50',
                'eval' => 'trim',
            ],
        ],

        'update_interval' => [
            'exclude' => 1,
            'displayCond' => 'FIELD:disable_auto_import:REQ:false',
            'label' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_db.xlf:tx_newsimporter_domain_model_importsource.update_interval',
            'config' => [
                'type' => 'datetime',
                'size' => 4,
                'default' => 7200,
                'format' => 'timesec',
            ],
        ],
    ],
];
