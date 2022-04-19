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
use Buepro\Easyconf\Mapper\MapperInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FormDataProvider implements FormDataProviderInterface, SingletonInterface
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
            // Include JS to hide new and delete button
            GeneralUtility::makeInstance(PageRenderer::class)
                ->loadRequireJsModule('TYPO3/CMS/Easyconf/FormDataProvider');
            // Read in properties
            foreach ($columns as $columnName => $columnConfig) {
                if (
                    ($mapperClass = $columnConfig['tx_easyconf']['mapper'] ?? '') !== '' &&
                    ($path = $columnConfig['tx_easyconf']['path'] ?? '') !== '' &&
                    class_exists($mapperClass) &&
                    ($mapper = GeneralUtility::makeInstance($mapperClass)) instanceof MapperInterface
                ) {
                    $value = $mapper->getProperty($path);
                    if (isset($columnConfig['tx_easyconf']['valueMap']) && is_array($columnConfig['tx_easyconf']['valueMap'])) {
                        $value = array_flip($columnConfig['tx_easyconf']['valueMap'])[$value] ?? $value;
                    }
                    $result['databaseRow'][$columnName] = $value;
                }
            }
            $event = new AfterReadingPropertiesEvent($result['databaseRow']);
            // @phpstan-ignore-next-line
            $result['databaseRow'] = $this->eventDispatcher->dispatch($event)->getFormFields();
        }
        return $result;
    }
}
