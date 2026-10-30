<?php

declare(strict_types=1);

defined('TYPO3') or die();

if (isset($GLOBALS['TCA']['tx_news_domain_model_news']['types']['2']['showitem'])) {
    $GLOBALS['TCA']['tx_news_domain_model_news']['types']['2']['showitem']
        = str_replace(
            'externalurl,',
            'bodytext,externalurl,',
            $GLOBALS['TCA']['tx_news_domain_model_news']['types']['2']['showitem']
        );
}
