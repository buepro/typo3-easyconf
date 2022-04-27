<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper\Service;

use Buepro\Easyconf\Service\DatabaseService;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EasyconfService implements SingletonInterface, MapperServiceInterface
{
    protected array $configuration = [];
    protected array $fields = [];

    public function init(int $pageUid): self
    {
        $this->configuration = (GeneralUtility::makeInstance(DatabaseService::class)
            ->getRecord('tx_easyconf_configuration', ['pid' => $pageUid]) ?? []);
        if (isset($this->configuration['fields']) && (string)$this->configuration['fields'] !== '') {
            try {
                $decoded = json_decode($this->configuration['fields'], true, 512, JSON_THROW_ON_ERROR);
                $this->fields = is_array($decoded) ? $decoded : [];
            } catch (\JsonException $e) {
                $this->fields = [];
            }
        }
        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }

    public function getFieldByPath(string $path): string
    {
        $result = '';
        if (ArrayUtility::isValidPath($this->getFields(), $path, '.')) {
            $result = ArrayUtility::getValueByPath($this->getFields(), $path, '.');
        }
        return is_string($result) ? $result : '';
    }

    public function persistFields(): self
    {
        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_easyconf_configuration')
            ->update(
                'tx_easyconf_configuration',
                ['fields' => \json_encode($this->fields)],
                ['uid' => (int)$this->configuration['uid']],
                [Connection::PARAM_INT]
            );
        $this->init((int)$this->configuration['pid']);
        return $this;
    }
}
