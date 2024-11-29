<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper\Service;

class SiteConfigurationService extends AbstractSiteConfigurationService
{

    public function load($identifier): array
    {
        return $this->siteConfiguration->load_withImportsNotProcessed($identifier);
    }

    public function write($identifier, $siteData): void
    {
        if($this->siteWriter !== null) {
            $this->siteWriter->write($this->getSite()->getIdentifier(), $siteData);
        } else {
            $this->siteConfiguration->write_withNoProcessing($this->getSite()->getIdentifier(), $siteData);
        }
    }
}
