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
use Buepro\Easyconf\Mapper\AbstractMapper;
use Buepro\Easyconf\Service\DatabaseService;
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
            $this->writeProperties($incomingFieldArray, (int)$id);
            $this->filterIncomingFieldArray($incomingFieldArray);
        }
    }

    protected function writeProperties(array $data, int $id): void
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
                    ($mapper = AbstractMapper::getInstance($mapperClass)) !== null
                ) {
                    $mapper->setProperty($path, $data[$columnName]);
                }
            }
            $this->eventDispatcher->dispatch(new BeforePersistingPropertiesEvent($data));
            foreach (AbstractMapper::getInstances() as $mapper) {
                $mapper->persistProperties();
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
