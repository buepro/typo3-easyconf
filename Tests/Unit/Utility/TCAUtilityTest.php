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
    public function testGetFieldsDataProvider(): array
    {
        $properties = ['foo', 'bar', 'fooBar'];
        return [
            'properties only' => [
                [$properties], ['foo', 'bar', 'foo_bar'],
            ],
            'with prefix' => [
                [$properties, 'pre'], ['pre_foo', 'pre_bar', 'pre_foo_bar'],
            ],
            'with field list' => [
                [$properties, '', ',new_bar_baz, fooBar'], ['foo', 'new_bar_baz', 'fooBar'],
            ],
            'with prefix and field list' => [
                [$properties, 'pre', ',new_bar_baz, fooBar'], ['pre_foo', 'pre_new_bar_baz', 'pre_fooBar'],
            ],
            'with linebreaks' => [
                [['foo', '--linebreak--', 'bar']], ['foo', '--linebreak--', 'bar']
            ],
            'with linebreaks and prefix' => [
                [['foo', '--linebreak--', 'bar'], 'pre'], ['pre_foo', '--linebreak--', 'pre_bar']
            ],
            'with linebreaks, prefix and field list' => [
                [['foo', '--linebreak--', 'bar'], 'pre', ', *linebreak*'], ['pre_foo', 'pre_*linebreak*', 'pre_bar']
            ],
            'with palette 1' => [
                [['foo', 'paletteTest']], ['foo', 'paletteTest']
            ],
            'with palette 2' => [
                [['foo', 'testPalette']], ['foo', 'testPalette']
            ],
            'with palette and prefix' => [
                [['foo', 'paletteTest'], 'pre'], ['pre_foo', 'paletteTest']
            ],
            'with palette, prefix and field list 1' => [
                [['foo', 'paletteTest'], 'pre', ',otherPalette'], ['pre_foo', 'otherPalette']
            ],
            'with palette, prefix and field list 2' => [
                [['foo', 'paletteTest'], 'pre', ',paletteAlt'], ['pre_foo', 'paletteAlt']
            ],
        ];
    }

    /**
     * @dataProvider testGetFieldsDataProvider
     */
    public function testGetFields(array $params, array $expected): void
    {
        self::assertSame($expected, TCAUtility::getFields(...$params));
    }

    public function testGetPropertyMap(): void
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
        self::assertSame($expected, TCAUtility::getPropertyMap(...$params));
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

    public function testGetPalette(): void
    {
        $propertyList = 'foo, fooBar';
        $expected = ['showitem' => 'foo, foo_bar'];
        self::assertSame($expected, TCAUtility::getPalette($propertyList));
    }

    public function testType(): void
    {
        $tabs = [
            'tab.foo' => '--palette--;;foo, bar, bar_baz, barBaz',
            'tab.sur' => 'sur, nor'
        ];
        $l10nFile = 'l10n.xlf';
        $expected = [
            'showitem' =>
                '--div--;l10n.xlf:tab.foo, --palette--;;foo, bar, bar_baz, barBaz, ' .
                '--div--;l10n.xlf:tab.sur, sur, nor',
        ];
        self::assertSame($expected, TCAUtility::getType($tabs, $l10nFile));
    }
}
