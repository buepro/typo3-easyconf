<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper;

use Buepro\Easyconf\Configuration\Service\SiteConfigurationService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SiteConfigurationMapper extends AbstractMapper implements SingletonInterface
{
    protected SiteConfigurationService $siteConfigurationService;

    public function __construct(SiteConfigurationService $siteConfigurationService)
    {
        $this->siteConfigurationService = $siteConfigurationService;
    }

    public function getProperty(string $mapProperty): string
    {
        [,$mapProperty] = GeneralUtility::trimExplode(':', $mapProperty);
        return $this->siteConfigurationService->getPropertyByPath($mapProperty);
    }

    public function persistProperties(): void
    {
        $siteData = $this->siteConfigurationService->getSiteData();
        foreach ($this->buffer as $mapProperty => $value) {
            [, $path] = GeneralUtility::trimExplode(':', $mapProperty);
            $siteData = ArrayUtility::setValueByPath($siteData, $path, $value, '.');
        }
        $this->siteConfigurationService->writeSiteData($siteData);
    }
}
