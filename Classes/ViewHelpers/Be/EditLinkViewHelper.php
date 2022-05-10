<?php

namespace BeechIt\NewsImporter\ViewHelpers\Be;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class EditLinkViewHelper
 */
class EditLinkViewHelper extends AbstractViewHelper
{

    /**
     * Render the onclick JavaScript for editing given fields of given record
     *
     * @return string
     */
    public function render()
    {
        $table = $this->arguments['table'];
        $uid = $this->arguments['uid'];
        $command = $this->arguments['command'];
        return GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('record_edit', [
            'edit' => [
                $table => [
                    $uid => 'edit',
                ],
            ],
            'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
        ]);
    }

    public function initializeArguments(): void
    {
        $this->registerArgument('table', 'string', '', true);
        $this->registerArgument('uid', 'int', '', true);
        $this->registerArgument('command', 'string', '', false, 'edit');
    }
}
