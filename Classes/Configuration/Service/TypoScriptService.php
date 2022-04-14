<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Configuration\Service;

use Buepro\Easyconf\Service\DatabaseService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

class TypoScriptService implements SingletonInterface
{
    protected int $pageUid = 0;
    protected int $treeLevel = 0;
    protected int $rootPageUid = 0;
    protected array $templateRow = [];
    protected array $constants = [];
    protected array $parentConstants = [];

    public function init(int $pageUid): self
    {
        $this->pageUid = $pageUid;
        $rootLine = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid)->get();
        $this->initializeActivePageProperties($rootLine);
        $this->initializeParentPageProperties($rootLine);
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

    protected function initializeParentPageProperties(array $rootLine): void
    {
        $parentPageUid = (int)(array_values($rootLine)[0]['pid'] ?? 0);
        if ($parentPageUid > 0) {
            $rootLineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $parentPageUid);
            $this->parentConstants = $this->getConstantsForRootLine(
                $rootLineUtility->get(),
                GeneralUtility::makeInstance(TemplateService::class)
            );
        }
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

    public function getParentConstants(): array
    {
        return $this->parentConstants;
    }

    public function getConstantByPath(string $path): string
    {
        $result = '';
        if (ArrayUtility::isValidPath($this->getConstants(), $path, '.')) {
            $result = ArrayUtility::getValueByPath($this->getConstants(), $path, '.');
        }
        return is_string($result) ? $result : '';
    }

    public function getParentConstantByPath(string $path): string
    {
        $result = '';
        if (ArrayUtility::isValidPath($this->getParentConstants(), $path, '.')) {
            $result = ArrayUtility::getValueByPath($this->getParentConstants(), $path, '.');
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
