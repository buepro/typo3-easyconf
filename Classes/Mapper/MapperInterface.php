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
    /**
     * Get the property value where the buffered value has precedence over the one from the storage.
     *
     * @param string $path
     * @return mixed
     */
    public function getProperty(string $path);

    /**
     * @param string $path
     * @param mixed $value
     * @return $this
     */
    public function bufferProperty(string $path, $value): self;

    public function removePropertyFromBuffer(string $path): self;

    public function persistBuffer(): self;
}
