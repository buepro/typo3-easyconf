<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Configuration\Service;

use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;
use Buepro\Easyconf\Service\DatabaseService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

class TypoScriptService implements SingletonInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected int $pageUid = 0;
    protected int $treeLevel = 0;
    protected int $rootPageUid = 0;
    protected array $templateRow = [];
    protected array $constants = [];
    protected array $inheritedConstants = [];

    public function init(int $pageUid): self
    {
        $this->pageUid = $pageUid;
        $rootLine = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid)->get();
        $this->initializeActivePageProperties($rootLine);
        $this->initializeInheritedConstants($rootLine);
        return $this;
    }

    protected function initializeActivePageProperties(array $rootLine): void
    {
        $this->rootPageUid = $rootLine[0]['uid'] ?? 0;
        $templateService = GeneralUtility::makeInstance(TemplateService::class);
        $this->constants = $this->getConstantsForRootLine($rootLine, $templateService);
        $this->treeLevel = $templateService->getRootlineLevel((string)$this->pageUid);
        $templateUid = (int)(array_reverse($templateService->hierarchyInfo)[0]['uid'] ?? 0);
        $this->templateRow = (GeneralUtility::makeInstance(DatabaseService::class)
            ->getRecord('sys_template', ['uid' => $templateUid]) ?? []);
    }

    protected function initializeInheritedConstants(array $rootLine): void
    {
        if (($tokenPos = strpos($this->templateRow['constants'], TypoScriptConstantMapper::TEMPLATE_TOKEN)) === false) {
            $this->inheritedConstants = $this->constants;
            return;
        }
        try {
            $constants = substr($this->templateRow['constants'], 0, $tokenPos);
            $this->updateTemplateConstants($constants);
            $rootLineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $this->pageUid);
            $this->inheritedConstants = $this->getConstantsForRootLine(
                $rootLineUtility->get(),
                GeneralUtility::makeInstance(TemplateService::class)
            );
        } catch (\Exception $e) {
            // @phpstan-ignore-next-line
            $this->logger->error('Inherited constants initialization error. Code: 1650372424');
        }
        $this->updateTemplateConstants($this->templateRow['constants']);
    }

    protected function updateTemplateConstants(string $constants): void
    {
        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('sys_template')
            ->update(
                'sys_template',
                ['constants' => $constants],
                ['uid' => (int)$this->templateRow['uid']],
                [Connection::PARAM_STR]
            );
    }

    protected function getConstantsForRootLine(array $rootLine, TemplateService $templateService): array
    {
        $templateService->runThroughTemplates($rootLine);
        $templateService->generateConfig();
        return GeneralUtility::makeInstance(\TYPO3\CMS\Core\TypoScript\TypoScriptService::class)
            ->convertTypoScriptArrayToPlainArray($templateService->setup_constants);
    }

    public function getConstants(): array
    {
        return $this->constants;
    }

    public function getInheritedConstants(): array
    {
        return $this->inheritedConstants;
    }

    public function getConstantByPath(string $path): string
    {
        $result = '';
        if (ArrayUtility::isValidPath($this->getConstants(), $path, '.')) {
            $result = ArrayUtility::getValueByPath($this->getConstants(), $path, '.');
        }
        return is_string($result) ? $result : '';
    }

    public function getInheritedConstantByPath(string $path): string
    {
        $result = '';
        if (ArrayUtility::isValidPath($this->getInheritedConstants(), $path, '.')) {
            $result = ArrayUtility::getValueByPath($this->getInheritedConstants(), $path, '.');
        }
        return is_string($result) ? $result : '';
    }

    public function getTemplateRow(): array
    {
        return $this->templateRow;
    }

    public function getRootPageUid(): int
    {
        return $this->rootPageUid;
    }

    public function getTreeLevel(): int
    {
        return $this->treeLevel;
    }
}
