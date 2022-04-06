<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class MapperFactory
{
    public const MAP_ID_TS_CONST = 'tsc';
    public const MAP_ID_SITE_CONF = 'site';

    public static function getMapper(string $mapProperty): ?AbstractMapper
    {
        [$specifier] = GeneralUtility::trimExplode(':', $mapProperty);
        if ($specifier === self::MAP_ID_TS_CONST) {
            return GeneralUtility::makeInstance(TypoScriptConstantMapper::class);
        }
        if ($specifier === self::MAP_ID_SITE_CONF) {
            return GeneralUtility::makeInstance(SiteConfigurationMapper::class);
        }
        return null;
    }

    /** @return AbstractMapper[] */
    public static function getMappers(): array
    {
        return [
            GeneralUtility::makeInstance(TypoScriptConstantMapper::class),
            GeneralUtility::makeInstance(SiteConfigurationMapper::class),
        ];
    }
}
