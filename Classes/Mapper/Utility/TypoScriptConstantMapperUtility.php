<?php
declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper\Utility;

use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;

class TypoScriptConstantMapperUtility
{
    public static function removeUnusedImportStatements(string $constants, int $pageUid, int $templateUid): string
    {
        $pattern = sprintf(
            "^%s[\\n\\r]{0,2}@import '[\w\/]*%s'",
            TypoScriptConstantMapper::TEMPLATE_TOKEN,
            str_replace('%d', '(\d+)', TypoScriptConstantMapper::FILE_NAME)
        );
        if ((int) preg_match_all("/$pattern/m", $constants, $matches) > 0) {
            $searches = [];
            foreach ($matches[0] as $i => $match) {
                if ((int)$matches[1][$i] === $pageUid && (int)$matches[2][$i] === $templateUid) {
                    continue;
                }
                $searches[] = $match;
            }
            if ($searches !== []) {
                $constants = str_replace($searches, '', $constants);
            }
        }
        return $constants;
    }
}
