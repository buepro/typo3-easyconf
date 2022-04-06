<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper;

use Buepro\Easyconf\Configuration\Service\TypoScriptService;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

class TypoScriptConstantMapper extends AbstractMapper implements SingletonInterface
{
    public const FILE_NAME = 'Constants';
    public const TEMPLATE_TOKEN = '# The following line has been added automatically by the extension easyconf';

    protected TypoScriptService $typoScriptService;
    protected string $fileLocation = 'fileadmin/tx_easyconf/Configuration/TypoScript/';

    public function __construct(TypoScriptService $typoScriptService)
    {
        $this->typoScriptService = $typoScriptService;
    }

    public function getProperty(string $mapProperty): string
    {
        [,$mapProperty] = GeneralUtility::trimExplode(':', $mapProperty);
        return $this->typoScriptService->getConstantByPath($mapProperty);
    }

    public function persistProperties(): void
    {
        GeneralUtility::writeFile($this->getFileWithAbsolutePath(), $this->getBufferContent());
        $this->updateTemplateRecord();
    }

    protected function getFileWithRelativePath(): ?string
    {
        return PathUtility::getRelativePath(Environment::getPublicPath(), $this->getFileWithAbsolutePath());
    }

    protected function getFileWithAbsolutePath(): string
    {
        $fileName = GeneralUtility::getFileAbsFileName(sprintf(
            '%s%s%d.typoscript',
            $this->fileLocation,
            self::FILE_NAME,
            (int)$this->typoScriptService->getTemplateRow()['uid']
        ));
        $dir = GeneralUtility::dirname($fileName);
        if (!file_exists($dir)) {
            GeneralUtility::mkdir_deep($dir);
        }
        return $fileName;
    }

    protected function getBufferContent(): string
    {
        $content = [];
        foreach ($this->buffer as $mapProperty => $value) {
            [, $path] = GeneralUtility::trimExplode(':', $mapProperty);
            $content[] = sprintf('%s = %s', $path, $value);
        }
        return implode("\r\n", $content);
    }

    protected function updateTemplateRecord(): void
    {
        $constants = $this->typoScriptService->getTemplateRow()['constants'];
        if (
            !str_contains($constants, self::TEMPLATE_TOKEN) &&
            ($fileName = $this->getFileWithRelativePath()) !== null
        ) {
            $constants .= "\r\n\r\n" . self::TEMPLATE_TOKEN . "\r\n";
            $constants .= sprintf("@import '%s'\r\n", $fileName);
            $templateUid = (int)$this->typoScriptService->getTemplateRow()['uid'];
            GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionForTable('sys_template')
                ->update(
                    'sys_template',
                    ['constants' => $constants],
                    ['uid' => $templateUid],
                    [Connection::PARAM_STR]
                );
        }
    }
}
