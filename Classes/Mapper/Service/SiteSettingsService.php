<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper\Service;

use TYPO3\CMS\Core\Site\Entity\Site;

class SiteSettingsService extends AbstractSiteConfigurationService
{

    public function load(string $identifier): array
    {
        /** @var Site|null $site */
        if($site = $this->siteConfiguration->resolveAllExistingSites(false)[$identifier] ?? null) {
            return $site->getSettings()->getAll();
        }
        return [];
    }

    public function write(string $identifier, array $siteData): void
    {
        $this->siteConfiguration->writeSettings($identifier, $siteData);
    }
}
