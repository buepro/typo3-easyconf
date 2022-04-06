<?php

declare(strict_types=1);

namespace Buepro\Easyconf\Event;

final class AfterReadingPropertiesEvent
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

    public function setFields(array $fields): self
    {
        $this->fields = $fields;
        return $this;
    }
}
