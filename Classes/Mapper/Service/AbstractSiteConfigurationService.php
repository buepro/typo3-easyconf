<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper\Service;

use Buepro\Easyconf\Configuration\SiteConfiguration;
use Buepro\Easyconf\Mapper\SiteSettingsMapper;
use TYPO3\CMS\Core\Configuration\Exception\SiteConfigurationWriteException;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

abstract class AbstractSiteConfigurationService implements SingletonInterface, MapperServiceInterface
{
    abstract public function load(string $identifier): array;
    abstract public function write(string $identifier, array $siteData): void;

    /** @var SiteConfiguration|null  */
    protected ?SiteConfiguration $siteConfiguration = null;
    protected ?SiteWriter $siteWriter = null;
    protected ?Site $site = null;
    protected array $siteData = [];

    public function setSiteConfigurationDataType(string $siteConfigurationDataType): void
    {
        $this->siteConfigurationDataType = $siteConfigurationDataType;
    }

    public function init(int $pageUid): self
    {
        $this->siteConfiguration = GeneralUtility::makeInstance(SiteConfiguration::class);
        if((new Typo3Version())->getMajorVersion() >= 13) {
            $this->siteWriter = GeneralUtility::makeInstance(SiteWriter::class);
        }
        $sites = $this->siteConfiguration->getAllExistingSites();
        $indexedSites = [];
        foreach ($sites as $site) {
            $indexedSites[$site->getRootPageId()] = $site;
        }
        // Get the first available site configuration in the root line
        $rootLine = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid)->get();
        foreach ($rootLine as $pageRecord) {
            if (isset($indexedSites[$pageRecord['uid']])) {
                $this->site = $indexedSites[$pageRecord['uid']];
                $this->siteData = $this->load($this->site->getIdentifier());
                return $this;
            }
        }
        return $this;
    }

    public function getSiteConfiguration(): ?SiteConfiguration
    {
        return $this->siteConfiguration;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function getSiteData(): array
    {
        return $this->siteData;
    }

    public function getPropertyByPath(string $path): array|string|int|float
    {
        if (
            ArrayUtility::isValidPath($this->siteData, $path, '.') &&
            (
                is_array($candidate = ArrayUtility::getValueByPath($this->siteData, $path, '.')) ||
                is_string($candidate) ||
                is_int($candidate) ||
                is_float($candidate)
            )
        ) {
            return $candidate;
        }
        return '';
    }

    /**
     * @param array $siteData
     * @return void
     */
    public function writeSiteData(array $siteData): void
    {
        if ($this->siteConfiguration !== null && $this->getSite() !== null) {
            $this->write($this->getSite()->getIdentifier(), $siteData);
            $this->siteData = $this->load($this->getSite()->getIdentifier());
        }
    }
}
