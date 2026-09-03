<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'News importer',
    'description' => 'Import RSS/Atom feeds or externals HTML as ext:news records',
    'category' => 'plugin',
    'version' => '2.0.0',
    'state' => 'alpha',
    'author' => 'Frans Saris',
    'author_email' => 't3ext@beech.it',
    'author_company' => 'Beech.it',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-13.4.99',
            'news' => '*',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
