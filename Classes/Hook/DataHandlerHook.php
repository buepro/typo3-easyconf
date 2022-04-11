<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Hook;

use Buepro\Easyconf\Configuration\ServiceManager;
use Buepro\Easyconf\Event\BeforePersistingPropertiesEvent;
use Buepro\Easyconf\Mapper\MapperFactory;
use Buepro\Easyconf\Utility\TCAUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataHandlerHook implements SingletonInterface
{
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
        if ($table === 'tx_easyconf_configuration') {
            $this->writeProperties($incomingFieldArray);
            $incomingFieldArray = [];
        }
    }

    protected function writeProperties(array $data): void
    {
        if (
            ($pageUid = (int)($data['pid'] ?? 0)) > 0 &&
            ($columns = $GLOBALS['TCA']['tx_easyconf_configuration']['columns'] ?? null) !== null &&
            GeneralUtility::makeInstance(ServiceManager::class)->init($pageUid)
        ) {
            foreach ($columns as $columnName => $columnConfig) {
                if (
                    isset($data[$columnName]) &&
                    ($mapProperty = $columnConfig[TCAUtility::MAPPING_PROPERTY] ?? null) !== null &&
                    ($mapper = MapperFactory::getMapper($mapProperty)) !== null
                ) {
                    $mapper->setProperty($data[$columnName], $mapProperty);
                }
            }
            $this->eventDispatcher->dispatch(new BeforePersistingPropertiesEvent($data));
            foreach (MapperFactory::getMappers() as $mapper) {
                $mapper->persistProperties();
            }
        }
    }
}
