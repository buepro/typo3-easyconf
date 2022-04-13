<?php

declare(strict_types = 1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Tests\Unit\Utility;

use Buepro\Easyconf\Utility\TcaUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class TcaUtilityTest extends UnitTestCase
{
    public function testGetFieldsDataProvider(): array
    {
        $properties = ['foo', 'bar', 'fooBar', 'subpath.to.bazBar'];
        return [
            'properties only' => [
                [$properties], ['foo', 'bar', 'foo_bar', 'subpath_to_baz_bar'],
            ],
            'with prefix' => [
                [$properties, 'pre'], ['pre_foo', 'pre_bar', 'pre_foo_bar', 'pre_subpath_to_baz_bar'],
            ],
            'with field list' => [
                [$properties, '', ',new_bar_baz, fooBar'], ['foo', 'new_bar_baz', 'fooBar', 'subpath_to_baz_bar'],
            ],
            'with prefix and field list' => [
                [$properties, 'pre', ',new_bar_baz, fooBar'], ['pre_foo', 'pre_new_bar_baz', 'pre_fooBar', 'pre_subpath_to_baz_bar'],
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
        self::assertSame($expected, TcaUtility::getFields(...$params));
    }

    public function testGetPropertyMap(): void
    {
        $params = [
            'mapper1',
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
            'mapper' => 'mapper1',
            'path' => 'path.to.property',
            'propertyFieldMap' => $propertyFieldMap,
            'fieldPropertyMap' => array_flip($propertyFieldMap),
        ];
        self::assertSame($expected, TcaUtility::getPropertyMap(...$params));
    }

    public function testGetColumns(): void
    {
        $propertyMaps = [
            TcaUtility::getPropertyMap(
                'mapper1',
                'path.to.properties',
                'foo, fooBar'
            )
        ];
        $l10nFile = 'l10n.xlf';
        $expected = [
            'foo' => [
                'label' => $l10nFile . ':' . 'foo',
                'tx_easyconf' => [
                    'mapper' => 'mapper1',
                    'path' => 'path.to.properties.foo'
                ],
                'config' => [
                    'type' => 'input',
                ],
            ],
            'foo_bar' => [
                'label' => $l10nFile . ':' . 'foo_bar',
                'tx_easyconf' => [
                    'mapper' => 'mapper1',
                    'path' => 'path.to.properties.fooBar'
                ],
                'config' => [
                    'type' => 'input',
                ],
            ],
        ];
        self::assertSame($expected, TcaUtility::getColumns($propertyMaps, $l10nFile));
    }

    public function testGetPalette(): void
    {
        $propertyList = 'foo, fooBar, baz';
        self::assertSame(
            ['showitem' => 'foo, foo_bar, --linebreak--, baz'],
            TcaUtility::getPalette($propertyList)
        );
        self::assertSame(
            ['showitem' => 'foo, foo_bar, baz'],
            TcaUtility::getPalette($propertyList, '', 0)
        );
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
        self::assertSame($expected, TcaUtility::getType($tabs, $l10nFile));
    }

    public function testModifyColumns(): void
    {
        $columns = [
            'field1' => [
                'label' => 'Field 1',
                'config' => ['type' => 'input'],
            ],
            'field2' => [
                'label' => 'Field 2',
                'config' => ['type' => 'input'],
            ],
            'field3' => [
                'label' => 'Field 3',
                'config' => ['type' => 'input'],
            ],
        ];
        $modifier = ['config' => ['renderType' => 'colorpicker']];
        $expected = [
            'field1' => [
                'label' => 'Field 1',
                'config' => ['type' => 'input', 'renderType' => 'colorpicker'],
            ],
            'field2' => [
                'label' => 'Field 2',
                'config' => ['type' => 'input'],
            ],
            'field3' => [
                'label' => 'Field 3',
                'config' => ['type' => 'input', 'renderType' => 'colorpicker'],
            ],
        ];
        TcaUtility::modifyColumns($columns, 'field1, field3', $modifier);
        self::assertSame($expected, $columns);
    }

    public function testExcludePropertiesDataProvider(): array
    {
        $propertyList = 'foo, bar, fooBar, foo_bar';
        return [
            'exclude none' => [$propertyList, '', $propertyList],
            'exclude first' => [$propertyList, 'foo', 'bar, fooBar, foo_bar'],
            'exclude between' => [$propertyList, 'bar', 'foo, fooBar, foo_bar'],
            'exclude last' => [$propertyList, 'foo_bar', 'foo, bar, fooBar'],
            'exclude several' => [$propertyList, 'foo, fooBar', 'bar, foo_bar'],
            'exclude all' => [$propertyList, $propertyList, ''],
        ];
    }

    /**
     * @dataProvider testExcludePropertiesDataProvider
     */
    public function testExcludeProperties(string $propertyList, string $excluded, string $expected): void
    {
        self::assertSame(
            $expected,
            TcaUtility::excludeProperties($propertyList, $excluded)
        );
    }
}
