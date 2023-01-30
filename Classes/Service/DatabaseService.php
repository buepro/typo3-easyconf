<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

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

    public function getRecord(string $table, array $constraint): ?array
    {
        $result = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable($table)
            ->select(
                ['*'],
                $table,
                $constraint
            )->fetchAssociative();
        return is_array($result) && $result !== [] ? $result : null;
    }

    public function addRecord(string $table, array $fields, array $types = []): ?array
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($table);
        $now = time();
        foreach (['tstamp', 'crdate'] as $fieldName) {
            if (!isset($fields[$fieldName])) {
                $fields[$fieldName] = $now;
            }
        }
        $connection->insert(
            $table,
            $fields,
            $types
        );
        return $this->getRecord($table, ['uid' => (int)$connection->lastInsertId('pages')]);
    }
}
