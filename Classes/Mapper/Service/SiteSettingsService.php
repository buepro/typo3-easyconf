<?php

namespace Buepro\Easyconf\Mapper\Service;

use TYPO3\CMS\Core\Site\Entity\Site;

class SiteSettingsService extends AbstractSiteConfigurationService
{

    public function load(string $identifier): array
    {
        /** @var Site|null $site */
        if($site = $this->siteConfiguration->resolveAllExistingSites(false)[$identifier] ?? null) {
            return $site->getSettings()->getAll();
        };
        return [];
    }

    public function write(string $identifier, array $siteData): void
    {
        $this->siteConfiguration->writeSettings($identifier, $siteData);
    }
}
