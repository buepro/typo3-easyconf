<?php

declare(strict_types=1);

namespace Buepro\Easyconf\Mapper;

abstract class AbstractMapper
{
    protected array $buffer = [];

    abstract public function getProperty(string $mapProperty): string;

    public function setProperty(string $value, string $mapProperty): void
    {
        $this->buffer[$mapProperty] = $value;
    }

    abstract public function persistProperties();
}
