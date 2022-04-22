<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Hook;

use Buepro\Easyconf\Event\BeforePersistingPropertiesEvent;
use Buepro\Easyconf\Mapper\MapperInterface;
use Buepro\Easyconf\Mapper\MapperRegistry;
use Buepro\Easyconf\Mapper\ServiceManager;
use Buepro\Easyconf\Service\DatabaseService;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class DataHandlerHook implements SingletonInterface
{
    /**
     * If this data is set all mapped properties will be persisted in processDatamap_afterAllOperations.
     *
     * @var ?array ['tableUid' => $id, 'formFields' => $incomingFieldArray]
     */
    protected static ?array $configurationData = null;

    protected EventDispatcherInterface $eventDispatcher;

    public function injectEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function processDatamap_preProcessFieldArray(
        array &$incomingFieldArray,
        string $table,
        string $id,
        DataHandler $dataHandler
    ): void {
        if (
            $table === 'tx_easyconf_configuration' &&
            $incomingFieldArray !== [] &&
            ($uid = (int)$id) > 0 &&
            MathUtility::canBeInterpretedAsInteger($id)
        ) {
            self::$configurationData = [
                'tableUid' => $uid,
                'formFields' =>$incomingFieldArray,
            ];
            $this->writePropertiesToBuffer($incomingFieldArray, $uid);
            $this->filterIncomingFieldArray($incomingFieldArray);
        }
    }

    public function processDatamap_afterAllOperations(DataHandler $dataHandler): void
    {
        if (
            self::$configurationData !== null &&
            is_array($configurationRecord = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable('tx_easyconf_configuration')
                ->select(['*'], 'tx_easyconf_configuration', ['uid' => self::$configurationData['tableUid']])
                ->fetchAssociative())
        ) {
            $this->eventDispatcher->dispatch(new BeforePersistingPropertiesEvent(
                self::$configurationData['formFields'],
                $configurationRecord
            ));
            foreach (MapperRegistry::getInstances() as $mapper) {
                $mapper->persistBuffer();
            }
            self::$configurationData = null;
        }
    }

    protected function writePropertiesToBuffer(array $data, int $id): void
    {
        if (
            ($columns = $GLOBALS['TCA']['tx_easyconf_configuration']['columns'] ?? null) !== null &&
            ($pageUid = GeneralUtility::makeInstance(DatabaseService::class)
                ->getField('tx_easyconf_configuration', 'pid', ['uid' => $id])) > 0 &&
            GeneralUtility::makeInstance(ServiceManager::class)->init($pageUid)
        ) {
            foreach ($columns as $columnName => $columnConfig) {
                if (
                    isset($data[$columnName]) &&
                    ($mapperClass = $columnConfig['tx_easyconf']['mapper'] ?? '') !== '' &&
                    ($path = $columnConfig['tx_easyconf']['path'] ?? '') !== '' &&
                    class_exists($mapperClass) &&
                    ($mapper = GeneralUtility::makeInstance($mapperClass)) instanceof MapperInterface
                ) {
                    $value = $data[$columnName];
                    $value = $columnConfig['tx_easyconf']['valueMap'][$value] ?? $value;
                    $mapper->bufferProperty($path, $value);
                }
            }
        }
    }

    protected function filterIncomingFieldArray(array &$incomingFieldArray): void
    {
        $allowedFields = array_flip(GeneralUtility::trimExplode(
            ',',
            $GLOBALS['TCA']['tx_easyconf_configuration']['ctrl']['EXT']['easyconf']['dataHandlerAllowedFields'] ?? ''
        ));
        $incomingFieldArray = array_filter(
            $incomingFieldArray,
            static fn ($field) => isset($allowedFields[$field]),
            ARRAY_FILTER_USE_KEY
        );
    }
}
