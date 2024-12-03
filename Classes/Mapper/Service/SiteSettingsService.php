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

    public function load(): array
    {
        /** @var Site|null $site */
        if($site = $this->siteConfiguration->resolveAllExistingSites(false)[$this->getSite()->getIdentifier()] ?? null) {
            return $site->getSettings()->getAll();
        }
        return [];
    }

    public function write(array $siteData): void
    {
        if($this->siteWriter !== null) {
            $this->siteWriter->writeSettings($this->getSite()->getIdentifier(), $siteData);
        } else {
            $this->siteConfiguration->write_withNoProcessing($this->getSite()->getIdentifier(), $siteData);
        }
    }
}
