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
    protected string $storage = 'fileadmin/easyconf/Configuration/TypoScript/';
    protected string $importStatementHandling = 'maintainAtEnd';

    public function __construct(TypoScriptService $typoScriptService)
    {
        parent::__construct();
        $this->typoScriptService = $typoScriptService;
        $fileLocation = $this->typoScriptService->getConstantByPath(
            'module.tx_easyconf.typoScriptConstantMapper.storage'
        );
        if ($fileLocation !== '' && GeneralUtility::validPathStr($fileLocation)) {
            $this->storage = str_ends_with($fileLocation, '/') ? $fileLocation : $fileLocation . '/';
        }
        $importStatementHandling = trim($this->typoScriptService->getConstantByPath(
            'module.tx_easyconf.typoScriptConstantMapper.importStatementHandling'
        ));
        if (in_array($importStatementHandling, ['addOnce', 'maintainAtEnd'], true)) {
            $this->importStatementHandling = $importStatementHandling;
        }
    }

    public function getProperty(string $path): string
    {
        return $this->typoScriptService->getConstantByPath($path);
    }

    public function getParentProperty(string $path): string
    {
        return $this->typoScriptService->getParentConstantByPath($path);
    }

    public function bufferProperty(string $path, string $value): void
    {
        if ($this->getParentProperty($path) !== $value) {
            $this->buffer[$path] = $value;
        }
    }

    public function persistBuffer(): void
    {
        if (count($this->buffer) === 0) {
            return;
        }
        GeneralUtility::writeFile($this->getFileWithAbsolutePath(), $this->getBufferContent());
        $this->addImportStatementToTemplateRecord();
    }

    protected function getFileWithRelativePath(): ?string
    {
        return PathUtility::getRelativePath(Environment::getPublicPath(), $this->getFileWithAbsolutePath());
    }

    protected function getFileWithAbsolutePath(): string
    {
        $fileName = GeneralUtility::getFileAbsFileName(sprintf(
            '%s%s%d.typoscript',
            $this->storage,
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
        ksort($this->buffer);
        foreach ($this->buffer as $path => $value) {
            $content[] = sprintf('%s = %s', $path, $value);
        }
        return implode("\r\n", $content);
    }

    protected function addImportStatementToTemplateRecord(): void
    {
        $fileName = $this->getFileWithRelativePath();
        if ($fileName === null) {
            return;
        }
        $constants = $this->typoScriptService->getTemplateRow()['constants'] ?? '';
        $tokenAndImportStatement = sprintf("%s\r\n@import '%s'", self::TEMPLATE_TOKEN, $fileName);
        $constantsContainsToken = strpos($constants, self::TEMPLATE_TOKEN) !== false;
        if ($constantsContainsToken && $this->importStatementHandling !== 'maintainAtEnd') {
            return;
        }
        if ($constantsContainsToken && $this->importStatementHandling === 'maintainAtEnd') {
            // Remove token with import statement
            $parts = GeneralUtility::trimExplode($tokenAndImportStatement, $constants, true);
            $constants = implode("\r\n", $parts);
        }
        $constants .= sprintf("\r\n\r\n%s", $tokenAndImportStatement);
        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_template')
            ->update(
                'sys_template',
                ['constants' => $constants],
                ['uid' => (int)$this->typoScriptService->getTemplateRow()['uid']],
                [Connection::PARAM_STR]
            );
    }
}
