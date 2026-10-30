<?php

declare(strict_types=1);

use BeechIt\NewsImporter\Controller\AdminController;

return [
    'web_NewsImporterNewsimporter' => [
        'parent' => 'web',
        'access' => 'user',
        'iconIdentifier' => 'apps-pagetree-folder-contains-imports',
        'labels' => 'LLL:EXT:news_importer/Resources/Private/Language/locallang_be_module.xlf',
        'extensionName' => 'NewsImporter',
        'controllerActions' => [
            AdminController::class => [
                'index',
                'show',
                'import',
            ],
        ],
    ],
];
