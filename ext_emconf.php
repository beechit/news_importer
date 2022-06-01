<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "news_importer"
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'News importer',
    'description' => 'Import RSS/Atom feeds or externals HTML as ext:news records',
    'category' => 'plugin',
    'version' => '2.0.0',
    'state' => 'alpha',
    'clearCacheOnLoad' => true,
    'author' => 'Frans Saris',
    'author_email' => 't3ext@beech.it',
    'author_company' => 'Beech.it',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.99.99',
            'news' => '*',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
