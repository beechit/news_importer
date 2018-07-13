<?php
defined('TYPO3_MODE') or die();

if (isset($GLOBALS['TCA']['tx_news_domain_model_news']['types']['2']['showitem'])) {

    $GLOBALS['TCA']['tx_news_domain_model_news']['types']['2']['showitem'] =
        str_replace(
            'externalurl,',
            'bodytext;;;richtext::rte_transform[flag=rte_disabled|mode=ts_css],externalurl,',
            $GLOBALS['TCA']['tx_news_domain_model_news']['types']['2']['showitem']);
}