<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "news_importer"
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
	'title' => 'News importer',
	'description' => 'Import RSS/Atom feeds or externals HTML as ext:news records',
	'category' => 'plugin',
	'version' => '1.0.1',
	'state' => 'alpha',
	'clearcacheonload' => TRUE,
	'author' => 'Frans Saris',
	'author_email' => 't3ext@beech.it',
	'author_company' => 'Beech.it',
	'constraints' => [
		'depends' => [
			'typo3' => '7.99.99-8.99.99',
			'news' => '*'
        ],
		'conflicts' => [],
		'suggests' => [],
    ],
];
