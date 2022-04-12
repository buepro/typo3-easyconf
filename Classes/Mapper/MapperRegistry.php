<?php

declare(strict_types = 1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper;

abstract class MapperRegistry
{
    /** @var MapperInterface[] $instances */
    protected static array $instances = [];

    public static function registerInstance(MapperInterface $mapper): void
    {
        if (!isset(self::$instances[get_class($mapper)])) {
            self::$instances[get_class($mapper)] = $mapper;
        }
    }

    /** @return MapperInterface[] */
    public static function getInstances(): array
    {
        return self::$instances;
    }
}
