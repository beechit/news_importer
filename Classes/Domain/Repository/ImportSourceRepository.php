<?php

declare(strict_types=1);

namespace BeechIt\NewsImporter\Domain\Repository;

use BeechIt\NewsImporter\Domain\Model\ImportSource;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * The repository for ImportSources
 *
 * @extends Repository<ImportSource>
 */
class ImportSourceRepository extends Repository
{
    /**
     * Disable respect storage
     */
    public function initializeObject(): void
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * Find all sources to import
     *
     * @return QueryResultInterface<int, ImportSource>
     */
    public function findSourcesToImport(int $limit = 0): QueryResultInterface
    {
        /** @var Query<ImportSource> $query */
        $query = $this->createQuery();
        $currentTime = (int)GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp', $_SERVER['REQUEST_TIME'] ?? time());

        $rawQuery = 'SELECT * FROM tx_newsimporter_domain_model_importsource WHERE';
        $rawQuery .= ' hidden = 0 AND deleted = 0 AND disable_auto_import = 0';
        $rawQuery .= ' AND last_run < (' . $currentTime . ' - update_interval)';
        $rawQuery .= ' AND (starttime = 0 OR starttime <= ' . $currentTime . ')';
        $rawQuery .= ' AND (endtime = 0 OR endtime > ' . $currentTime . ')';
        $rawQuery .= ' ORDER BY last_run ASC';

        if ($limit > 0) {
            $rawQuery .= ' LIMIT ' . $limit;
        }

        $query->statement($rawQuery);
        return $query->execute();
    }
}
