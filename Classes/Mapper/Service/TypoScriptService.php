<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Mapper\Service;

use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;
use Buepro\Easyconf\Utility\GeneralUtility as EasyconfGeneralUtility;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TypoScript\IncludeTree\SysTemplateRepository;
use TYPO3\CMS\Core\TypoScript\IncludeTree\SysTemplateTreeBuilder;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Traverser\ConditionVerdictAwareIncludeTreeTraverser;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Visitor\IncludeTreeCommentAwareAstBuilderVisitor;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Visitor\IncludeTreeConditionEnforcerVisitor;
use TYPO3\CMS\Core\TypoScript\Tokenizer\LosslessTokenizer;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Tstemplate\Controller\ActiveTypoScriptController;

class TypoScriptService implements SingletonInterface, MapperServiceInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected int $pageUid = 0;
    protected ?Site $site = null;
    protected int $treeLevel = 0;
    protected int $rootPageUid = 0;
    protected array $templateRow = [];
    protected array $constants = [];
    //Constants that would be present without easyconf
    protected array $inheritedConstants = [];

    public function __construct(
        private readonly SysTemplateRepository $sysTemplateRepository,
        private readonly SysTemplateTreeBuilder $treeBuilder
    ) {
    }

    public function init(int $pageUid): self
    {
        $this->pageUid = $pageUid;
        $this->site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageUid);
        $rootLine = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid)->get();
        $this->initializeActivePageProperties($rootLine);
        $this->initializeInheritedConstants($rootLine);
        return $this;
    }

    protected function initializeActivePageProperties(array $rootLine): void
    {
        $this->rootPageUid = $rootLine[0]['uid'] ?? 0;
        $this->constants = $this->getConstantsForRootLine($rootLine);
        $this->treeLevel = count($rootLine) - 1;
        $sysTemplateRows = $this->sysTemplateRepository->getSysTemplateRowsByRootline($rootLine);
        $this->templateRow = $sysTemplateRows[count($sysTemplateRows) - 1];
    }

    protected function initializeInheritedConstants(array $rootLine): void
    {
        if (($tokenPos = strpos((string)$this->templateRow['constants'], TypoScriptConstantMapper::TEMPLATE_TOKEN)) === false) {
            $this->inheritedConstants = $this->constants;
            return;
        }
        try {
            $before = substr($this->templateRow['constants'], 0, $tokenPos);
            $after = explode(
                LF,
                EasyconfGeneralUtility::convertToUnixLineBreaks(substr($this->templateRow['constants'], $tokenPos))
            );
            array_shift($after);
            array_shift($after);
            $constants = implode(LF, [$before, ...$after]);

            $this->updateTemplateConstants($constants);
            $rootLineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $this->pageUid);
            $this->inheritedConstants = $this->getConstantsForRootLine($rootLineUtility->get());
        } catch (\Exception $e) {
            /**
             * @phpstan-ignore-next-line
             * @extensionScannerIgnoreLine
             */
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

    /**
     * @see ActiveTypoScriptController::indexAction
     */
    protected function getConstantsForRootLine(array $rootLine): array
    {
        $sysTemplateRows = $this->sysTemplateRepository->getSysTemplateRowsByRootline($rootLine);
        $constantIncludeTree = $this->treeBuilder->getTreeBySysTemplateRowsAndSite(
            'constants',
            $sysTemplateRows,
            new LosslessTokenizer(),
            $this->site
        );
        $treeTraverser = new ConditionVerdictAwareIncludeTreeTraverser();
        $treeTraverserVisitors = [];
        $conditionEnforcerVisitor = new IncludeTreeConditionEnforcerVisitor();
        $conditionEnforcerVisitor->setEnabledConditions([]);
        $treeTraverserVisitors[] = $conditionEnforcerVisitor;
        $constantAstBuilderVisitor = GeneralUtility::makeInstance(IncludeTreeCommentAwareAstBuilderVisitor::class);
        $treeTraverserVisitors[] = $constantAstBuilderVisitor;
        $treeTraverser->traverse($constantIncludeTree, $treeTraverserVisitors);
        $constantsAst = $constantAstBuilderVisitor->getAst();
        return GeneralUtility::removeDotsFromTS($constantsAst->toArray());
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
