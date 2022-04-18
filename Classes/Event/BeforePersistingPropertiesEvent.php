<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Event;

final class BeforePersistingPropertiesEvent
{
    protected array $formFields;
    protected array $configurationRecord;

    public function __construct(array $formFields, array $configurationRecord)
    {
        $this->formFields = $formFields;
        $this->configurationRecord = $configurationRecord;
    }

    public function getFormFields(): array
    {
        return $this->formFields;
    }

    public function getConfigurationRecord(): array
    {
        return $this->configurationRecord;
    }
}
