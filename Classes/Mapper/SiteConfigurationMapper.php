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

class SiteConfigurationMapper extends AbstractMapper implements SingletonInterface
{
    protected SiteConfigurationService $siteConfigurationService;

    public function __construct(SiteConfigurationService $siteConfigurationService)
    {
        parent::__construct();
        $this->siteConfigurationService = $siteConfigurationService;
    }

    public function getProperty(string $path)
    {
        return $this->siteConfigurationService->getPropertyByPath($path);
    }

    public function persistBuffer(): MapperInterface
    {
        if (count($this->buffer) === 0) {
            return $this;
        }
        $siteData = $this->siteConfigurationService->getSiteData();
        foreach ($this->buffer as $path => $value) {
            $siteData = ArrayUtility::setValueByPath($siteData, $path, $value, '.');
        }
        $this->siteConfigurationService->writeSiteData($siteData);
        return $this;
    }
}
