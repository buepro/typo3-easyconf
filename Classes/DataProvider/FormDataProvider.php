<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\DataProvider;

use Buepro\Easyconf\Configuration\ServiceManager;
use Buepro\Easyconf\Event\AfterReadingPropertiesEvent;
use Buepro\Easyconf\Mapper\MapperFactory;
use Buepro\Easyconf\Utility\TCAUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FormDataProvider implements FormDataProviderInterface
{
    protected EventDispatcherInterface $eventDispatcher;

    public function injectEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @inheritDoc
     */
    public function addData(array $result): array
    {
        if (
            $result['tableName'] === 'tx_easyconf_configuration' &&
            ($pageUid = (int)($result['parentPageRow']['uid'] ?? 0)) > 0 &&
            ($columns = $GLOBALS['TCA']['tx_easyconf_configuration']['columns'] ?? null) !== null &&
            GeneralUtility::makeInstance(ServiceManager::class)->init($pageUid)
        ) {
            foreach ($columns as $columnName => $columnConfig) {
                if (
                    ($mapProperty = $columnConfig[TCAUtility::MAPPING_PROPERTY] ?? null) !== null &&
                    ($mapper = MapperFactory::getMapper($mapProperty)) !== null
                ) {
                    $result['databaseRow'][$columnName] = $mapper->getProperty($mapProperty);
                }
            }
            $result = $this->eventDispatcher->dispatch(new AfterReadingPropertiesEvent($result))->getFields();
        }
        return $result;
    }
}
