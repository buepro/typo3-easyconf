<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationRepository
{
    public function getFirstByPid(int $pid): ?array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_easyconf_configuration')
            ->createQueryBuilder();
        $result = $queryBuilder
            ->select('*')
            ->from('tx_easyconf_configuration')
            ->where(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter($pid, \TYPO3\CMS\Core\Database\Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAssociative();
        return is_array($result) ? $result : null;
    }
}
