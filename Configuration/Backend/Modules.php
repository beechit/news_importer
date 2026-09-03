<?php

return [
    'web_NewsImporterNewsimporter' => [
        'parent' => 'web',
        'access' => 'user',
        'labels' => null,
        'extensionName' => 'NewsImporter',
        'controllerActions' => [
            \BeechIt\NewsImporter\Controller\AdminController::class => [
                'index',
                'show',
                'import',
            ],
        ],
    ],
];
