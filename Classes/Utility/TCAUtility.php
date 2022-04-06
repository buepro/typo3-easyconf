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

    public static function getFieldsToPropertyMap(
        string $mapId,
        string $mapPath,
        string $propertyList,
        string $fieldList = '',
        string $fieldPrefix = ''
    ): array {
        $properties = GeneralUtility::trimExplode(',', $propertyList);
        $fields = array_map(
            static fn (string $property): string => GeneralUtility::camelCaseToLowerCaseUnderscored($property),
            $properties
        );
        if ($fieldList !== '') {
            $fields = array_replace($fields, GeneralUtility::trimExplode(',', $fieldList));
        }
        if ($fieldPrefix !== '') {
            $fields = array_map(
                static fn (string $field): string => $fieldPrefix . '_' . $field,
                $fields
            );
        }
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
                $column = $field;
                $result[$column] = [
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

    public static function getPalettes(array $palettes): array
    {
        $result = [];
        foreach ($palettes as $palette => $fields) {
            $result[$palette] = ['showitem' => $fields];
        }
        return $result;
    }

    public static function getType(array $type, string $l10nFile): array
    {
        $tabs = [];
        foreach ($type as $tab => $properties) {
            $fields = [];
            foreach (GeneralUtility::trimExplode(',', $properties) as $propertyOrPalette) {
                if (strpos($propertyOrPalette, '--palette--') === 0) {
                    $fields[] = $propertyOrPalette;
                    continue;
                }
                $fields[] = GeneralUtility::camelCaseToLowerCaseUnderscored($propertyOrPalette);
            }
            $tabs[] = '--div--;' . $l10nFile . ':' . $tab . ',' . implode(', ', $fields);
        }
        return ['showitem' => implode(', ', $tabs)];
    }

    public static function getConfiguration(
        array $propertyMaps,
        array $palettes,
        array $type,
        string $l10nFile
    ): array {
        return [
            self::getColumns($propertyMaps, $l10nFile),
            self::getPalettes($palettes),
            self::getType($type, $l10nFile),
        ];
    }
}
