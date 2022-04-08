<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Tests\Unit\Mapper;

use Buepro\Easyconf\Configuration\Service\SiteConfigurationService;
use Buepro\Easyconf\Configuration\Service\TypoScriptService;
use Buepro\Easyconf\Mapper\AbstractMapper;
use Buepro\Easyconf\Mapper\MapperFactory;
use Buepro\Easyconf\Mapper\SiteConfigurationMapper;
use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class MapperFactoryTest extends UnitTestCase
{
    use ProphecyTrait;

    public function getMapperReturnsNullDataProvider(): array
    {
        return [
            'empty mapProperty' => ['', null],
            'not available map id1' => ['na', null],
            'not available map id2' => ['na:path.to.property', null],
            'wrong format' => [MapperFactory::MAP_ID_TS_CONST . '.path.to.property', null],
        ];
    }

    /**
     * @dataProvider getMapperReturnsNullDataProvider
     * @test
     */
    public function getMapperReturnsNull(string $mapProperty, ?AbstractMapper $expected): void
    {
        self::assertSame($expected, MapperFactory::getMapper($mapProperty));
    }

    /**
     * @test
     */
    public function getMapperReturnsTypoScriptConstantMapper(): void
    {
        $this->resetSingletonInstances = true;
        // @phpstan-ignore-next-line
        $typoScriptServiceProphecy = $this->prophesize(TypoScriptService::class);
        // @phpstan-ignore-next-line
        $typoScriptServiceProphecy->getConstantByPath(Argument::type('string'))->willReturn('');
        // @phpstan-ignore-next-line
        $typoScriptConstantMapper = new TypoScriptConstantMapper($typoScriptServiceProphecy->reveal());
        GeneralUtility::setSingletonInstance(TypoScriptConstantMapper::class, $typoScriptConstantMapper);
        self::assertSame(
            $typoScriptConstantMapper,
            MapperFactory::getMapper(MapperFactory::MAP_ID_TS_CONST)
        );
        self::assertSame(
            $typoScriptConstantMapper,
            MapperFactory::getMapper(MapperFactory::MAP_ID_TS_CONST . ':path.to.property')
        );
    }

    /**
     * @test
     */
    public function getMapperReturnsSiteConfigurationMapper(): void
    {
        $this->resetSingletonInstances = true;
        $siteConfigurationMapper = new SiteConfigurationMapper(new SiteConfigurationService());
        GeneralUtility::setSingletonInstance(SiteConfigurationMapper::class, $siteConfigurationMapper);
        self::assertSame(
            $siteConfigurationMapper,
            MapperFactory::getMapper(MapperFactory::MAP_ID_SITE_CONF)
        );
        self::assertSame(
            $siteConfigurationMapper,
            MapperFactory::getMapper(MapperFactory::MAP_ID_SITE_CONF . ':path.to.property')
        );
    }
}
