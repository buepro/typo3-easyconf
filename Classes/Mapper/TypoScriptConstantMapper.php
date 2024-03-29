<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper;

use Buepro\Easyconf\Data\PropertyFieldMap;
use Buepro\Easyconf\Mapper\Service\TypoScriptService;
use Buepro\Easyconf\Mapper\Utility\TypoScriptConstantMapperUtility;
use Buepro\Easyconf\Service\FileService;
use Buepro\Easyconf\Utility\GeneralUtility as EasyconfGeneralUtility;
use Buepro\Easyconf\Utility\TcaUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

class TypoScriptConstantMapper extends AbstractMapper implements SingletonInterface
{
    public const RELATIVE_STORAGE_TS_PATH = 'module.tx_easyconf.persistence.storageRelativeTypoScriptPath';
    public const IMPORT_STATEMENT_HANDLING_TS_PATH = 'module.tx_easyconf.settings.typoScriptConstantMapper.importStatementHandling';
    public const FILE_NAME = 'EasyconfConstantsP%dT%d.typoscript';
    public const TEMPLATE_TOKEN = '# The following line has been added automatically by the extension easyconf';
    public const PROPERTY_BUFFER_KEY = 'properties';
    public const SCRIPT_BUFFER_KEY = 'scripts';

    protected array $buffer = [self::PROPERTY_BUFFER_KEY => [], self::SCRIPT_BUFFER_KEY => []];
    protected string $storage = 'fileadmin/easyconf/Configuration/TypoScript/';
    protected string $importStatementHandling = 'maintainAtEnd';
    protected TypoScriptService $typoScriptService;
    protected FileService $fileService;
    protected PropertyFieldMap $propertyFieldMap;

    public function __construct(
        TypoScriptService $typoScriptService,
        FileService $fileService,
        PropertyFieldMap $propertyFieldMap
    ) {
        parent::__construct();
        $this->typoScriptService = $typoScriptService;
        $this->fileService = $fileService;
        $this->propertyFieldMap = $propertyFieldMap;
        $this->initializeStorage()->initializeImportStatementHandling();
    }

    protected function initializeStorage(): self
    {
        if (($storage = $this->fileService->getFullPath(self::RELATIVE_STORAGE_TS_PATH)) !== '') {
            $this->storage = $storage;
        }
        return $this;
    }

    protected function initializeImportStatementHandling(): self
    {
        $importStatementHandling = trim($this->typoScriptService->getConstantByPath(
            self::IMPORT_STATEMENT_HANDLING_TS_PATH
        ));
        if (in_array($importStatementHandling, ['addOnce', 'maintainAtEnd'], true)) {
            $this->importStatementHandling = $importStatementHandling;
        }
        return $this;
    }

    public function getProperty(string $path): string
    {
        return $this->buffer[self::PROPERTY_BUFFER_KEY][$path] ?? $this->typoScriptService->getConstantByPath($path);
    }

    public function getBufferedProperty(string $path): ?string
    {
        return $this->buffer[self::PROPERTY_BUFFER_KEY][$path] ?? null;
    }

    public function getInheritedProperty(string $path): string
    {
        return $this->typoScriptService->getInheritedConstantByPath($path);
    }

    public function bufferProperty(string $path, $value): MapperInterface
    {
        $this->removePropertyFromBuffer($path);
        $mapAlways = ($fieldName = $this->propertyFieldMap->getFieldName($path)) !== null &&
            (bool)(TcaUtility::getColumnConfiguration($fieldName)['mapAlways'] ?? false);
        if ($mapAlways || $this->getInheritedProperty($path) !== $value) {
            $this->buffer[self::PROPERTY_BUFFER_KEY][$path] = $value;
        }
        return $this;
    }

    public function removePropertyFromBuffer(string $path): MapperInterface
    {
        unset($this->buffer[self::PROPERTY_BUFFER_KEY][$path]);
        return $this;
    }

    public function bufferScript(string $script): MapperInterface
    {
        $this->buffer[self::SCRIPT_BUFFER_KEY][md5($script)] = $script;
        return $this;
    }

    public function persistBuffer(): MapperInterface
    {
        EasyconfGeneralUtility::writeTextFile($this->getFileWithAbsolutePath(), $this->getBufferContent());
        $this->addImportStatementToTemplateRecord();
        return $this;
    }

    protected function getFileWithRelativePath(): ?string
    {
        $targetDir = PathUtility::dirname($this->getFileWithAbsolutePath());
        $fileName = PathUtility::basename($this->getFileWithAbsolutePath());
        $relativePath = PathUtility::getRelativePath(Environment::getPublicPath(), $targetDir);
        return $relativePath . $fileName;
    }

    protected function getFileWithAbsolutePath(): string
    {
        return GeneralUtility::getFileAbsFileName(
            $this->storage . $this->fileService->getTemplateFileName(self::FILE_NAME)
        );
    }

    protected function getBufferContent(): string
    {
        $content = [];
        $content[] = sprintf(
            '# Generated by easyconf for page %d with template %d',
            $this->typoScriptService->getTemplateRow()['pid'],
            $this->typoScriptService->getTemplateRow()['uid']
        );
        ksort($this->buffer[self::PROPERTY_BUFFER_KEY]);
        foreach ($this->buffer[self::PROPERTY_BUFFER_KEY] as $path => $value) {
            $content[] = sprintf('%s = %s', $path, $value);
        }
        foreach ($this->buffer[self::SCRIPT_BUFFER_KEY] as $value) {
            $content[] = $value;
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
        $constants = TypoScriptConstantMapperUtility::removeUnusedImportStatements(
            $constants,
            $this->typoScriptService->getTemplateRow()['pid'],
            $this->typoScriptService->getTemplateRow()['uid'],
        );
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
