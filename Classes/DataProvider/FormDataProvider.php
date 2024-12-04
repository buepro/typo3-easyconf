<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\DataProvider;

use Buepro\Easyconf\Event\AfterReadingPropertiesEvent;
use Buepro\Easyconf\Mapper\EasyconfMapper;
use Buepro\Easyconf\Mapper\MapperInterface;
use Buepro\Easyconf\Mapper\ServiceManager;
use Buepro\Easyconf\Mapper\SiteSettingsMapper;
use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;
use Buepro\Easyconf\Utility\TcaUtility;
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
            /** @extensionScannerIgnoreLine */
            GeneralUtility::makeInstance(ServiceManager::class)->init($pageUid)
        ) {
            // Include JS to hide new and delete button
            GeneralUtility::makeInstance(PageRenderer::class)
                ->loadJavaScriptModule('@buepro/easyconf/FormDataProvider.js');
            // Read in properties
            foreach ($columns as $columnName => $columnConfig) {
                if (
                    ($path = TcaUtility::getMappingPath($columnName)) !== null &&
                    ($class = TcaUtility::getMappingClass($columnName)) !== null &&
                    ($mapper = GeneralUtility::makeInstance($class)) instanceof MapperInterface
                ) {
                    /** @var EasyconfMapper|SiteSettingsMapper|TypoScriptConstantMapper $mapper */
                    $value = $mapper->getProperty($path);
                    if($columnName != 'group') {
                        $result['databaseRow'][$columnName] = TcaUtility::mapMapperToFormValue($columnName, $value);
                    }
                }
            }
            $event = new AfterReadingPropertiesEvent($result['databaseRow']);
            // @phpstan-ignore-next-line
            $result['databaseRow'] = $this->eventDispatcher->dispatch($event)->getFormFields();
        }
        if($result['databaseRow']['group'] ?? false) {
            $result['recordTypeValue'] = $result['databaseRow']['group'] ?: 0;
        }
        return $result;
    }
}
