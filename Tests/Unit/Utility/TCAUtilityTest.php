<?php

declare(strict_types = 1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Tests\Unit\Utility;

use Buepro\Easyconf\Utility\TCAUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class TCAUtilityTest extends UnitTestCase
{
    public function testGetPropertyMapDataProvider(): array
    {
        $params = [
            'amap',
            'path.to.property',
            'foo, bar, fooBar',
            '',
            '',
        ];
        $propertyFieldMap = [
            'foo' => 'foo',
            'bar' => 'bar',
            'fooBar' => 'foo_bar',
        ];
        $expected = [
            'mapId' => 'amap',
            'mapPath' => 'path.to.property',
            'propertyFieldMap' => $propertyFieldMap,
            'fieldPropertyMap' => array_flip($propertyFieldMap),
        ];
        return [
            'minimal params' => [$params, $expected],
            'different field names' => [
                array_replace($params, [3 => 'baz_foo,,fooBarBaz']),
                array_replace($expected, [
                    'propertyFieldMap' => ($pfm = ['foo' => 'baz_foo', 'bar' => 'bar', 'fooBar' => 'fooBarBaz']),
                    'fieldPropertyMap' => array_flip($pfm),
                ])
            ],
            'prefix' => [
                array_replace($params, [4 => 'pre']),
                array_replace($expected, [
                    'propertyFieldMap' => ($pfm = ['foo' => 'pre_foo', 'bar' => 'pre_bar', 'fooBar' => 'pre_foo_bar']),
                    'fieldPropertyMap' => array_flip($pfm),
                ])
            ],
            'different field names and prefix' => [
                array_replace($params, [3 => 'baz_foo,,fooBarBaz', 4 => 'pre']),
                array_replace($expected, [
                    'propertyFieldMap' => ($pfm = ['foo' => 'pre_baz_foo', 'bar' => 'pre_bar', 'fooBar' => 'pre_fooBarBaz']),
                    'fieldPropertyMap' => array_flip($pfm),
                ])
            ],
        ];
    }

    /**
     * @dataProvider testGetPropertyMapDataProvider
     */
    public function testGetPropertyMap(array $parameters, array $expected): void
    {
        self::assertSame($expected, TCAUtility::getPropertyMap(...$parameters));
    }

    public function testGetColumns(): void
    {
        $propertyMaps = [
            TCAUtility::getPropertyMap(
                'map1',
                'path.to.properties',
                'foo, fooBar'
            )
        ];
        $l10nFile = 'l10n.xlf';
        $expected = [
            'foo' => [
                'label' => $l10nFile . ':' . 'foo',
                TCAUtility::MAPPING_PROPERTY => 'map1:path.to.properties.foo',
                'config' => [
                    'type' => 'input',
                ],
            ],
            'foo_bar' => [
                'label' => $l10nFile . ':' . 'foo_bar',
                TCAUtility::MAPPING_PROPERTY => 'map1:path.to.properties.fooBar',
                'config' => [
                    'type' => 'input',
                ],
            ],
        ];
        self::assertSame($expected, TCAUtility::getColumns($propertyMaps, $l10nFile));
    }

    public function testGetPalettes(): void
    {
        $palettes = [
            'group' => 'foo, fooBar',
        ];
        $expected = [
            'group' => ['showitem' => 'foo, fooBar'],
        ];
        self::assertSame($expected, TCAUtility::getPalettes($palettes));
    }

    public function testType(): void
    {
        $type = [
            'tab.foo' => '--palette--;;foo, bar, bazBar, bar_baz',
            'tab.bar' => 'bar'
        ];
        $l10nFile = 'l10n.xlf';
        $expected = [
            'showitem' =>
                '--div--;l10n.xlf:tab.foo, --palette--;;foo, bar, baz_bar, bar_baz, ' .
                '--div--;l10n.xlf:tab.bar, bar',
        ];
        self::assertSame($expected, TCAUtility::getType($type, $l10nFile));
    }

    public function testGetConfiguration(): void
    {
        $propertyMaps = [
            TCAUtility::getPropertyMap(
                'map1',
                'path.to.properties',
                'foo, fooBar'
            )
        ];
        $palettes = [
            'group' => 'foo, fooBar',
        ];
        $type = [
            'tab.foo' => '--palette--;;foo, bar, bazBar, bar_baz',
            'tab.bar' => 'bar'
        ];
        $l10nFile = 'l10n.xlf';
        $actual = TCAUtility::getConfiguration($propertyMaps, $palettes, $type, $l10nFile);
        self::assertCount(3, $actual);
        self::assertSame('foo', array_key_first($actual[0]));
        self::assertSame('group', array_key_first($actual[1]));
        self::assertSame('showitem', array_key_first($actual[2]));
    }
}
