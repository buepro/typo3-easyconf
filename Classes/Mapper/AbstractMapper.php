<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper;

abstract class AbstractMapper implements MapperInterface
{
    protected array $buffer = [];

    public function __construct()
    {
        MapperRegistry::registerInstance($this);
    }

    public function bufferProperty(string $path, $value): MapperInterface
    {
        $this->buffer[$path] = $value;
        return $this;
    }

    public function removePropertyFromBuffer(string $path): MapperInterface
    {
        unset($this->buffer[$path]);
        return $this;
    }
}
