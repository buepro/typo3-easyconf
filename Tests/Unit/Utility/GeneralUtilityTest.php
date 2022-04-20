<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Tests\Unit\Utility;

use Buepro\Easyconf\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class GeneralUtilityTest extends UnitTestCase
{
    public function testTrimRelativePathDataProvider(): array
    {
        return [
            'empty' => ['', ''],
            'empty absolute' => ['/', ''],
            'correct' => ['path/to/something/', 'path/to/something/'],
            'missing trailing slash' => ['path/to/something', 'path/to/something/'],
            'with leading slash' => ['/path/to/something/', 'path/to/something/'],
            'with spaces' => [' path/to/something/ ', 'path/to/something/'],
            'with spaces and slashes' => [' /path/to/something ', 'path/to/something/'],
        ];
    }

    /**
     * @dataProvider testTrimRelativePathDataProvider
     */
    public function testTrimRelativePath(string $path, string $expected): void
    {
        self::assertSame($expected, GeneralUtility::trimRelativePath($path));
    }

    public function testConvertToUnixLineBreaks(): void
    {
        self::assertSame(
            "Line1\nLine2\nLine3",
            GeneralUtility::convertToUnixLineBreaks("Line1\nLine2\r\nLine3")
        );
    }

    public function testConvertToWindowsLineBreaks(): void
    {
        self::assertSame(
            "Line1\r\nLine2\r\nLine3",
            GeneralUtility::convertToWindowsLineBreaks("Line1\nLine2\r\nLine3")
        );
    }
}
