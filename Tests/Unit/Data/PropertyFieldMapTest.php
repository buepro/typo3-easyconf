<?php
declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Tests\Unit\Data;

use Buepro\Easyconf\Data\PropertyFieldMap;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PropertyFieldMapTest extends \TYPO3\TestingFramework\Core\Unit\UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $GLOBALS['TCA']['tx_easyconf_configuration']['columns'] = [
            'foo' => [
                'tx_easyconf' => [
                    'path' => 'path.to.foo'
                ],
            ],
            'foo_bar' => [
                'tx_easyconf' => [
                    'path' => 'path.to.fooBar'
                ],
            ],
        ];
    }

    /**
     * @test
     */
    public function getFieldNameReturnsFieldNameOrNull(): void
    {
        $this->resetSingletonInstances = true;
        $propertyFieldMap = GeneralUtility::makeInstance(PropertyFieldMap::class);
        self::assertSame('foo', $propertyFieldMap->getFieldName('path.to.foo'));
        self::assertSame('foo_bar', $propertyFieldMap->getFieldName('path.to.fooBar'));
        self::assertSame(null, $propertyFieldMap->getFieldName('path.to.baz'));
    }

    /**
     * @test
     */
    public function getPropertyPathReturnsPropertyPathOrNull(): void
    {
        $this->resetSingletonInstances = true;
        $propertyFieldMap = GeneralUtility::makeInstance(PropertyFieldMap::class);
        self::assertSame('path.to.foo', $propertyFieldMap->getPropertyPath('foo'));
        self::assertSame('path.to.fooBar', $propertyFieldMap->getPropertyPath('foo_bar'));
        self::assertSame(null, $propertyFieldMap->getPropertyPath('baz'));
    }
}
