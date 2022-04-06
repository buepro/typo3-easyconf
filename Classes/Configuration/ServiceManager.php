<?php

declare(strict_types=1);

namespace Buepro\Easyconf\Configuration;

use Buepro\Easyconf\Configuration\Service\SiteConfigurationService;
use Buepro\Easyconf\Configuration\Service\TypoScriptService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ServiceManager implements SingletonInterface
{
    protected ?TypoScriptService $typoScriptService;
    protected ?SiteConfigurationService $siteConfigurationService;

    public function init(int $pageUid): bool
    {
        $this->typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class)->init($pageUid);
        if (($rootPageUid = $this->typoScriptService->getRootPageUid()) > 0) {
            $this->siteConfigurationService = GeneralUtility::makeInstance(SiteConfigurationService::class)
                ->init($rootPageUid);
        }
        return $this->servicesAvailable();
    }

    public function servicesAvailable(): bool
    {
        return $this->typoScriptService !== null && $this->siteConfigurationService !== null;
    }

    public function getTypoScriptService(): ?TypoScriptService
    {
        return $this->typoScriptService;
    }

    public function getSiteConfigurationService(): ?SiteConfigurationService
    {
        return $this->siteConfigurationService;
    }
}
