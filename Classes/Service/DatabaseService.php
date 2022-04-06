<?php

namespace Buepro\Easyconf\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DatabaseService
{
    /**
     * @return false|mixed
     */
    public function getField(string $table, string $field, array $constraint)
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($table)
            ->select(
                [$field],
                $table,
                $constraint
            )->fetchOne();
    }
}
