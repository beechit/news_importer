<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "news_importer"
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'News importer',
	'description' => 'Import RSS/Atom feeds or externals HTML as ext:news records',
	'category' => 'plugin',
	'version' => '0.1.2',
	'state' => 'alpha',
	'clearcacheonload' => TRUE,
	'author' => 'Frans Saris',
	'author_email' => 't3ext@beech.it',
	'author_company' => 'Beech.it',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.12-7.99.99',
			'news' => '*'
		),
		'conflicts' => array(),
		'suggests' => array(),
	),
);