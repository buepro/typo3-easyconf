<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class TCAUtility
{
    public static function getFields(
        array $properties,
        string $fieldPrefix = '',
        string $fieldList = ''
    ): array {
        $fields = array_map(
            static fn (string $property): string =>
                stripos($property, 'palette') === false ?
                    GeneralUtility::camelCaseToLowerCaseUnderscored($property) :
                    $property,
            $properties
        );
        if ($fieldList !== '') {
            $fields = array_replace($fields, array_filter(
                GeneralUtility::trimExplode(',', $fieldList),
                static fn ($item): bool => $item !== ''
            ));
        }
        if ($fieldPrefix !== '') {
            $fields = array_map(
                static fn (string $field): string =>
                ($field === '--linebreak--') || stripos($field, 'palette') !== false ?
                    $field :
                    $fieldPrefix . '_' . $field,
                $fields
            );
        }
        return $fields;
    }

    public static function getFieldList(
        string $propertyList,
        string $fieldPrefix = '',
        string $fieldList = ''
    ): string {
        return implode(', ', self::getFields(
            GeneralUtility::trimExplode(',', $propertyList, true),
            $fieldPrefix,
            $fieldList
        ));
    }

    public static function getPropertyMap(
        string $mapper,
        string $path,
        string $propertyList,
        string $fieldPrefix = '',
        string $fieldList = ''
    ): array {
        $properties = GeneralUtility::trimExplode(',', $propertyList);
        $fields = self::getFields($properties, $fieldPrefix, $fieldList);
        return [
            'mapper' => $mapper,
            'path' => $path,
            'propertyFieldMap' => array_combine($properties, $fields),
            'fieldPropertyMap' => array_combine($fields, $properties),
        ];
    }

    public static function getColumns(array $propertyMaps, string $l10nFile): array
    {
        $result = [];
        foreach ($propertyMaps as $propertyMap) {
            foreach ($propertyMap['fieldPropertyMap'] as $field => $property) {
                $result[$field] = [
                    'label' => $l10nFile . ':' . $field,
                    'tx_easyconf' => [
                        'mapper' => $propertyMap['mapper'],
                        'path' => $propertyMap['path'] . '.' . $property,
                    ],
                    'config' => [
                        'type' => 'input',
                    ],
                ];
            }
        }
        return $result;
    }

    public static function getPalette(
        string $propertyList,
        string $fieldPrefix = '',
        int $lineBreakPeriod = 2,
        string $fieldList = ''
    ): array {
        if ($lineBreakPeriod > 0) {
            $properties = GeneralUtility::trimExplode(',', $propertyList);
            $propertiesWithLineBreaks = [];
            foreach ($properties as $key => $value) {
                if ($key > 0 && $key % $lineBreakPeriod === 0) {
                    $propertiesWithLineBreaks[] = '--linebreak--';
                }
                $propertiesWithLineBreaks[] = $value;
            }
            $propertyList = implode(', ', $propertiesWithLineBreaks);
        }
        return ['showitem' => self::getFieldList($propertyList, $fieldPrefix, $fieldList)];
    }

    public static function getType(array $tabs, string $l10nFile): array
    {
        $localizedTabs = [];
        foreach ($tabs as $tabName => $tabItemList) {
            $localizedTabs[] = '--div--;' . $l10nFile . ':' . $tabName . ', ' . $tabItemList;
        }
        return ['showitem' => implode(', ', $localizedTabs)];
    }

    public static function modifyColumns(
        array &$columns,
        string $propertyList,
        array $modifier,
        string $fieldPrefix = '',
        string $fieldList = ''
    ): void {
        $fields = GeneralUtility::trimExplode(
            ',',
            self::getFieldList($propertyList, $fieldPrefix, $fieldList),
            true
        );
        foreach ($fields as $fieldName) {
            if (isset($columns[$fieldName])) {
                $columns[$fieldName] = array_replace_recursive($columns[$fieldName], $modifier);
            }
        }
    }

    public static function excludeProperties(string $propertyList, string $excludeList): string
    {
        $properties = GeneralUtility::trimExplode(',', $propertyList, true);
        $excluded = array_flip(GeneralUtility::trimExplode(',', $excludeList, true));
        $properties = array_filter($properties, static fn ($prop) => !isset($excluded[$prop]));
        return implode(', ', $properties);
    }
}
