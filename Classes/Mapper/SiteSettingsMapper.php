<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper;

use Buepro\Easyconf\Mapper\Service\SiteConfigurationService;
use Buepro\Easyconf\Mapper\Service\SiteSettingsService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;

class SiteSettingsMapper extends AbstractSiteConfigurationMapper implements SingletonInterface
{
    public function __construct(SiteSettingsService $siteConfigurationService)
    {
        $this->siteConfigurationService = $siteConfigurationService;
        parent::__construct();
    }
}
