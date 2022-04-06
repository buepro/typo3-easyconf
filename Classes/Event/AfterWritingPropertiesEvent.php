<?php

declare(strict_types=1);

namespace Buepro\Easyconf\Event;

final class AfterWritingPropertiesEvent
{
    protected array $fields;

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function getFields(): array
    {
        return $this->fields;
    }
}
