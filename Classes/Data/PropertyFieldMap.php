<?php
declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Data;

use TYPO3\CMS\Core\SingletonInterface;

class PropertyFieldMap implements SingletonInterface
{
    /** @var string[]  */
    protected array $propertyFieldMap = [];
    /** @var string[] */
    protected array $fieldPropertyMap = [];

    public function __construct()
    {
        foreach ($GLOBALS['TCA']['tx_easyconf_configuration']['columns'] as $fieldName => $fieldConfig) {
            if (($path = $fieldConfig['tx_easyconf']['path'] ?? '') !== '') {
                $this->fieldPropertyMap[$fieldName] = $path;
            }
        }
        $this->propertyFieldMap = array_flip($this->fieldPropertyMap);
    }

    public function getFieldName(string $propertyPath): ?string
    {
        return $this->propertyFieldMap[$propertyPath] ?? null;
    }

    public function getPropertyPath(string $fieldName): ?string
    {
        return $this->fieldPropertyMap[$fieldName] ?? null;
    }
}
