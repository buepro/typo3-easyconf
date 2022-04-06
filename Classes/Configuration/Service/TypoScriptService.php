<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Configuration\Service;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\TypoScript\ExtendedTemplateService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

class TypoScriptService implements SingletonInterface
{
    protected ?ExtendedTemplateService $templateService;
    protected ?array $templateRow;
    protected array $constants = [];

    public function init(int $pageUid): self
    {
        $this->templateService = GeneralUtility::makeInstance(ExtendedTemplateService::class);
        $this->templateRow = $this->templateService->ext_getFirstTemplate($pageUid);
        $rootlineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid);
        $rootLine = $rootlineUtility->get();
        $this->templateService->runThroughTemplates($rootLine);
        $this->templateService->generateConfig();
        return $this;
    }

    public function getConstants(): array
    {
        if ($this->constants !== []) {
            return $this->constants;
        }
        if (is_array($this->templateService->setup_constants)) {
            $this->constants = GeneralUtility::makeInstance(\TYPO3\CMS\Core\TypoScript\TypoScriptService::class)
                ->convertTypoScriptArrayToPlainArray($this->templateService->setup_constants);
        }
        return $this->constants;
    }

    public function getConstantByPath(string $path): string
    {
        $result = '';
        if (ArrayUtility::isValidPath($this->getConstants(), $path, '.')) {
            $result = ArrayUtility::getValueByPath($this->getConstants(), $path, '.');
        }
        return is_string($result) ? $result : '';
    }

    public function getTemplateRow(): array
    {
        return $this->templateRow;
    }

    public function getRootPageUid(): int
    {
        return $this->templateService->getRootId();
    }
}
