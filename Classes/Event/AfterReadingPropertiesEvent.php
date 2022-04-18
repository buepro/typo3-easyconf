<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Event;

final class AfterReadingPropertiesEvent
{
    protected array $formFields;

    public function __construct(array $formFields)
    {
        $this->formFields = $formFields;
    }

    public function getFormFields(): array
    {
        return $this->formFields;
    }

    public function setFormFields(array $formFields): self
    {
        $this->formFields = $formFields;
        return $this;
    }
}
