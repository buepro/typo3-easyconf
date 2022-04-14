<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper;

interface MapperInterface
{
    public function getProperty(string $path): string;

    public function bufferProperty(string $path, string $value): void;

    public function removePropertyFromBuffer(string $path): void;

    public function persistBuffer(): void;
}
