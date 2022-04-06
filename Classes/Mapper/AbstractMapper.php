<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper;

abstract class AbstractMapper
{
    protected array $buffer = [];

    abstract public function getProperty(string $mapProperty): string;

    public function setProperty(string $value, string $mapProperty): void
    {
        $this->buffer[$mapProperty] = $value;
    }

    abstract public function persistProperties(): void;
}
