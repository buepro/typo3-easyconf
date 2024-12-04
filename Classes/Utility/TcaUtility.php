<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Utility;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Columns contain the field `tx_easyconf` with the following structure:
 *      'my_field' => [
 *          ...
 *          'tx_easyconf' => [
 *              'mapper' => 'Buepro\Easyconf\Mapper\TypoScriptConstantMapper',
 *              'path' => 'path.to.myField',
 *              'valueMap' => [
 *                  0 => 'false',  // path.to.myField = false
 *                  1 => 'true',   // path.to.myField = true
 *              ],
 *          ],
 *      ]
 */
class TcaUtility
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
        $fields = array_map(static fn (string $field) => str_replace('.', '_', $field), $fields);
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

    /**
     * @param string $mapper
     * @param string $path
     * @param string $propertyList
     * @param string $fieldPrefix
     * @param string $fieldList
     * @param string $labelList optional - if present, then overrides LLL path
     * @return array
     */
    public static function getPropertyMap(
        string $mapper,
        string $path,
        string $propertyList,
        string $fieldPrefix = '',
        string $fieldList = '',
        string $labelList = ''
    ): array {
        $properties = GeneralUtility::trimExplode(',', $propertyList);
        $fields = self::getFields($properties, $fieldPrefix, $fieldList);
        $fieldLabelMap = $labelList ? array_combine($fields, GeneralUtility::trimExplode(',', $labelList)) : [];
        return [
            'mapper' => $mapper,
            'path' => $path,
            'propertyFieldMap' => array_combine($properties, $fields),
            'fieldPropertyMap' => array_combine($fields, $properties),
            'fieldLabelMap' => $fieldLabelMap,
        ];
    }

    public static function getColumns(array $propertyMaps, string $l10nFile): array
    {
        $result = [];
        foreach ($propertyMaps as $propertyMap) {
            foreach ($propertyMap['fieldPropertyMap'] as $field => $property) {
                $result[$field] = [
                    'label' => $propertyMap['fieldLabelMap'][$field] ?? $l10nFile . ':' . $field,
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

    public static function getType(array $tabs, string $l10nFile, string $type = '0', $cardIcon = ''): array
    {
        $localizedTabs = [];
        foreach ($tabs as $tabName => $tabItemList) {
            $localizedTabs[] = '--div--;' . $l10nFile . ':' . $tabName . ', ' . $tabItemList;
        }
        return [
            'showitem' => implode(', ', $localizedTabs),
            'title' => $l10nFile . ':type_' . $type . '_title',
            'subtitle' => $l10nFile . ':type_' . $type . '_subtitle',
            'description' => $l10nFile . ':type_' . $type . '_description',
            'cardIcon' => $cardIcon,
        ];
    }

    public static function addType(string $type, array $tabs, string $l10nFile, string $cardIcon = ''): void
    {
        $configuration = self::getType($tabs, $l10nFile, $type, $cardIcon);
        $GLOBALS['TCA']['tx_easyconf_configuration']['types'][$type] = $configuration;
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

    public static function getMappingPath(string $field): ?string
    {
        $path = $GLOBALS['TCA']['tx_easyconf_configuration']['columns'][$field]['tx_easyconf']['path'] ?? '';
        return $path !== '' ? $path : null;
    }

    public static function getMappingClass(string $field): ?string
    {
        $class = $GLOBALS['TCA']['tx_easyconf_configuration']['columns'][$field]['tx_easyconf']['mapper'] ?? '';
        return  class_exists($class) ? $class : null;
    }

    public static function getColumnConfiguration(string $field): ?array
    {
        return $GLOBALS['TCA']['tx_easyconf_configuration']['columns'][$field]['tx_easyconf'] ?? null;
    }

    public static function mapMapperToFormValue(string $field, bool|float|int|string $mapperValue): bool|float|int|string
    {
        if (
            ($configuration = self::getColumnConfiguration($field)) !== null &&
            isset($configuration['valueMap']) && is_array($configuration['valueMap'])
        ) {
            return array_flip($configuration['valueMap'])[strval($mapperValue)] ?? $mapperValue;
        }
        return $mapperValue;
    }

    public static function mapFormToMapperValue(string $field, bool|float|int|string $formValue): bool|float|int|string
    {
        if (
            ($configuration = $GLOBALS['TCA']['tx_easyconf_configuration']['columns'][$field]['tx_easyconf']) !== null &&
            isset($configuration['valueMap']) && is_array($configuration['valueMap'])
        ) {
            return $configuration['valueMap'][strval($formValue)] ?? $formValue;
        }
        return $formValue;
    }
}
