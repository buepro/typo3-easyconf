<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper;

use Buepro\Easyconf\Mapper\Service\EasyconfService;
use Buepro\Easyconf\Mapper\Service\SiteConfigurationService;
use Buepro\Easyconf\Mapper\Service\SiteSettingsService;
use Buepro\Easyconf\Mapper\Service\TypoScriptService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ServiceManager implements SingletonInterface
{
    protected ?TypoScriptService $typoScriptService;
    protected ?SiteConfigurationService $siteConfigurationService;
    protected ?SiteSettingsService $siteSettingsService;
    protected ?EasyconfService $easyconfService;

    public function init(int $pageUid): bool
    {
        /** @extensionScannerIgnoreLine */
        $this->typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class)->init($pageUid);
        /** @extensionScannerIgnoreLine */
        $this->siteConfigurationService = GeneralUtility::makeInstance(SiteConfigurationService::class)->init($pageUid);
        /** @extensionScannerIgnoreLine */
        $this->siteSettingsService = GeneralUtility::makeInstance(SiteSettingsService::class)->init($pageUid);
        /** @extensionScannerIgnoreLine */
        $this->easyconfService = GeneralUtility::makeInstance(EasyconfService::class)->init($pageUid);
        return $this->servicesAvailable();
    }

    public function servicesAvailable(): bool
    {
        return $this->typoScriptService !== null && $this->siteConfigurationService !== null &&
            $this->easyconfService !== null;
    }

    public function getTypoScriptService(): ?TypoScriptService
    {
        return $this->typoScriptService;
    }

    public function getSiteConfigurationService(): ?SiteConfigurationService
    {
        return $this->siteConfigurationService;
    }

    public function getEasyconfService(): ?EasyconfService
    {
        return $this->easyconfService;
    }
}
