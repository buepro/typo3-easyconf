<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Service;

use Buepro\Easyconf\Configuration\Service\TypoScriptService;
use Buepro\Easyconf\Utility\GeneralUtility as EasyconfGeneralUtility;
use TYPO3\CMS\Core\SingletonInterface;

class FileService implements SingletonInterface
{
    protected TypoScriptService $typoScriptService;

    public function __construct(TypoScriptService $typoScriptService)
    {
        $this->typoScriptService = $typoScriptService;
    }

    /**
     * @param string $fileName Must contain a placeholder for the root page uid
     */
    public function getRootFileName(string $fileName): string
    {
        return sprintf(
            $fileName,
            $this->typoScriptService->getRootPageUid()
        );
    }

    /**
     * @param string $fileName Must contain placeholders for the page and template uid
     */
    public function getTemplateFileName(string $fileName): string
    {
        return sprintf(
            $fileName,
            $this->typoScriptService->getTemplateRow()['pid'],
            $this->typoScriptService->getTemplateRow()['uid']
        );
    }

    public function getRootPath(): string
    {
        return EasyconfGeneralUtility::trimRelativePath($this->typoScriptService->getConstantByPath(
            'module.tx_easyconf.general.storageRoot'
        ));
    }

    public function getSegmentPath(string $typoScriptPath): string
    {
        return EasyconfGeneralUtility::trimRelativePath($this->typoScriptService->getConstantByPath($typoScriptPath));
    }

    public function getFullPath(string $segmentTypoScriptPath): string
    {
        return $this->getRootPath() . $this->getSegmentPath($segmentTypoScriptPath);
    }
}
