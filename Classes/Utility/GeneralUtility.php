<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Utility;

use Buepro\Easyconf\Utility\GeneralUtility as EasyconfGeneralUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility as CoreGeneralUtility;

class GeneralUtility
{
    /**
     * @return string Relative path in the form "relative/path/" or ""
     */
    public static function trimRelativePath(string $path): string
    {
        $result = trim($path, " \t\n\r\0\x0B/") . '/';
        return $result === '/' ? '' : $result;
    }

    public static function convertToUnixLineBreaks(string $text): string
    {
        return str_replace(CR, '', $text);
    }

    public static function convertToWindowsLineBreaks(string $text): string
    {
        return str_replace(LF, CRLF, self::convertToUnixLineBreaks($text));
    }

    public static function readTextFile(string $fileName): string
    {
        $content = @file_get_contents($fileName);
        return is_string($content) ? self::convertToUnixLineBreaks($content) : '';
    }

    /**
     * Ensures the path to the file to be written exists and writes
     * the content with windows type line breaks to the file.
     *
     * @see GeneralUtility::writeFile()
     */
    public static function writeTextFile(string $fileName, string $content, bool $changePermissions = false): bool
    {
        $dir = CoreGeneralUtility::dirname($fileName);
        if (!file_exists($dir)) {
            CoreGeneralUtility::mkdir_deep($dir);
        }
        return CoreGeneralUtility::writeFile(
            $fileName,
            EasyconfGeneralUtility::convertToWindowsLineBreaks($content),
            $changePermissions
        );
    }
}
