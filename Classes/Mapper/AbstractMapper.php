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

abstract class AbstractMapper
{
    protected array $buffer = [];
    /** @var AbstractMapper[] $instances */
    protected static array $instances = [];

    public static function getInstance(string $className): ?self
    {
        if (!class_exists($className)) {
            return null;
        }
        if (
            !isset(self::$instances[$className]) &&
            ($mapper = GeneralUtility::makeInstance($className)) instanceof self
        ) {
            self::$instances[$className] = $mapper;
        }
        return self::$instances[$className];
    }

    /** @return AbstractMapper[] */
    public static function getInstances(): array
    {
        return self::$instances;
    }

    abstract public function getProperty(string $path): string;

    public function setProperty(string $path, string $value): void
    {
        $this->buffer[$path] = $value;
    }

    abstract public function persistProperties(): void;
}
