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
    public const MAPPING_PROPERTY = 'txEasyconfMapping';

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
        string $mapId,
        string $mapPath,
        string $propertyList,
        string $fieldPrefix = '',
        string $fieldList = ''
    ): array {
        $properties = GeneralUtility::trimExplode(',', $propertyList);
        $fields = self::getFields($properties, $fieldPrefix, $fieldList);
        return [
            'mapId' => $mapId,
            'mapPath' => $mapPath,
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
                    self::MAPPING_PROPERTY => $propertyMap['mapId'] . ':' . $propertyMap['mapPath'] . '.' . $property,
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
        string $fieldList = ''
    ): array {
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
}
