<?php
declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Tests\Unit\Mapper\Utility;

use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;
use Buepro\Easyconf\Mapper\Utility\TypoScriptConstantMapperUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class TypoScriptConstantMapperUtilityTest extends UnitTestCase
{
    private static function getImportStatement(int $pageUid, int $templateUid): string
    {
        return sprintf(
            "\n%s\n@import 'path/to/file/%s'\n",
            TypoScriptConstantMapper::TEMPLATE_TOKEN,
            sprintf(TypoScriptConstantMapper::FILE_NAME, $pageUid, $templateUid)
        );
    }

    public static function removeUnusedImportStatementsResultContainsOnlyUsedImportStatementDataProvider(): array
    {
        return [
            'no import statement' => ['foo=1', 1, 1, 'foo=1'],
            'only used import statement' => [
                $constants = self::getImportStatement(1, 1),
                1,
                1,
                $constants
            ],
            'only unused import statement' => [
                $constants = self::getImportStatement(1, 2),
                1,
                1,
                "\n\n"
            ],
            'with unused import statement' => [
                implode("\n", [
                    "foo=1\n\n",
                    self::getImportStatement(1, 10),
                    "bar=2\n\n",
                    self::getImportStatement(1, 1),
                    'baz=3'
                ]),
                1,
                1,
                implode("\n", [
                    "foo=1\n\n",
                    "\n\n",
                    "bar=2\n\n",
                    self::getImportStatement(1, 1),
                    'baz=3'
                ]),
            ],
        ];
    }

    /**
     * @dataProvider removeUnusedImportStatementsResultContainsOnlyUsedImportStatementDataProvider
     * @test
     */
    public function removeUnusedImportStatementsResultContainsOnlyUsedImportStatement(
        string $constants,
        int $pageUid,
        int $templateUid,
        string $expected
    ): void {
        self::assertSame($expected, TypoScriptConstantMapperUtility::removeUnusedImportStatements(
            $constants,
            $pageUid,
            $templateUid
        ));
    }
}
