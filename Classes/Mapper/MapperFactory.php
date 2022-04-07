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

    protected static array $mapIdClassMap = [
        self::MAP_ID_TS_CONST => TypoScriptConstantMapper::class,
        self::MAP_ID_SITE_CONF => SiteConfigurationMapper::class,
    ];

    public static function getMapper(string $mapProperty): ?AbstractMapper
    {
        [$mapId] = GeneralUtility::trimExplode(':', $mapProperty);
        if (
            isset(self::$mapIdClassMap[$mapId]) &&
            ($mapper = GeneralUtility::makeInstance(self::$mapIdClassMap[$mapId])) instanceof AbstractMapper
        ) {
            return $mapper;
        }
        return null;
    }

    /** @return AbstractMapper[] */
    public static function getMappers(): array
    {
        $result = array_map(static fn ($class) => GeneralUtility::makeInstance($class), self::$mapIdClassMap);
        return array_filter($result, static fn ($object) => $object instanceof AbstractMapper);
    }
}
