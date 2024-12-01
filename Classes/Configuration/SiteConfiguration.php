<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Configuration;

use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Configuration\Event\SiteConfigurationBeforeWriteEvent;
use TYPO3\CMS\Core\Configuration\Exception\SiteConfigurationWriteException;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SiteConfiguration extends \TYPO3\CMS\Core\Configuration\SiteConfiguration
{
    public function getSiteSettingsPublic($identifier, $siteData): \TYPO3\CMS\Core\Site\Entity\SiteSettings
    {
        return $this->getSiteSettings($identifier, $siteData);
    }

    /**
     * @param string $siteIdentifier
     * @return array
     */
    public function load_withImportsNotProcessed(string $siteIdentifier): array
    {
        $fileName = $this->configPath . '/' . $siteIdentifier . '/' . $this->configFileName;
        $loader = GeneralUtility::makeInstance(YamlFileLoader::class);
        return $loader->load(GeneralUtility::fixWindowsFilePath($fileName), 0);
    }

    /**
     * Add or update a site configuration
     *
     * @param bool $protectPlaceholders whether to disallow introducing new placeholders
     * @todo enforce $protectPlaceholders with TYPO3 v13.0
     * @throws SiteConfigurationWriteException
     */
    public function write_withNoProcessing(string $siteIdentifier, array $configuration, bool $protectPlaceholders = false): void
    {
        $folder = $this->configPath . '/' . $siteIdentifier;
        $fileName = $folder . '/' . $this->configFileName;
        $newConfiguration = $configuration;
        if (!file_exists($folder)) {
            GeneralUtility::mkdir_deep($folder);
            if ($protectPlaceholders && $newConfiguration !== []) {
                $newConfiguration = $this->protectPlaceholders([], $newConfiguration);
            }
        } elseif (file_exists($fileName)) {
            $loader = GeneralUtility::makeInstance(YamlFileLoader::class);
            // load without any processing to have the unprocessed base to modify
            $newConfiguration = $loader->load(GeneralUtility::fixWindowsFilePath($fileName), 0);
            // Out commented:
            /*            // load the processed configuration to diff changed values
                        $processed = $loader->load(GeneralUtility::fixWindowsFilePath($fileName));
                        // find properties that were modified via GUI
                        $newModified = array_replace_recursive(
                            self::findRemoved($processed, $configuration),
                            self::findModified($processed, $configuration)
                        );
                        if ($protectPlaceholders && $newModified !== []) {
                            $newModified = $this->protectPlaceholders($newConfiguration, $newModified);
                        }*/
            // 1 line added
            $newModified = $configuration;
            // change _only_ the modified keys, leave the original non-changed areas alone
            ArrayUtility::mergeRecursiveWithOverrule($newConfiguration, $newModified);
        }
        $event = $this->eventDispatcher->dispatch(new SiteConfigurationBeforeWriteEvent($siteIdentifier, $newConfiguration));
        $newConfiguration = $this->sortConfiguration($event->getConfiguration());
        $yamlFileContents = Yaml::dump($newConfiguration, 99, 2);
        if (!GeneralUtility::writeFile($fileName, $yamlFileContents)) {
            throw new SiteConfigurationWriteException('Unable to write site configuration in sites/' . $siteIdentifier . '/' . $this->configFileName, 1590487011);
        }
        $this->firstLevelCache = null;
        $this->cache->remove($this->cacheIdentifier);
    }
}
