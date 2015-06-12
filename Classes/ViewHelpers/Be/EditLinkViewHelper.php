<?php
namespace BeechIt\NewsImporter\ViewHelpers\Be;

/*
 * This source file is proprietary property of Beech Applications B.V.
 * Date: 11-03-2015 12:07
 * All code (c) Beech Applications B.V. all rights reserved
 */

/**
 * Class EditLinkViewHelper
 */
class EditLinkViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * Render the onclick JavaScript for editing given fields of given record
	 *
	 * @param string $table
	 * @param int $uid
	 * @param string $command
	 * @return string
	 */
	public function render($table, $uid, $command = 'edit') {
		$content = 'window.location.href=\'alt_doc.php?returnUrl=\'+T3_THIS_LOCATION+\'&edit['. $table . '][' .
			(int)$uid .
			']=' . $command . '&disHelp=1\';return false;';
		return $content;
	}
}