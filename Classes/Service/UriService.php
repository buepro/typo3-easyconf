<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Service;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class UriService
{
    /** @var UriBuilder $uriBuilder */
    protected $uriBuilder;

    public function __construct()
    {
        $this->uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
    }

    public function getEditUri(array $configuration, bool $withPreview = false): string
    {
        $uid = (int)$configuration['uid'];
        $pid = (int)$configuration['pid'];
        $params = [
            'edit' => ['tx_easyconf_configuration' => [$uid => 'edit']],
            'returnUrl' => $this->getInfoUri($pid),
        ];
        if ($withPreview) {
            $params['showPreview'] = true;
            $params['popViewId'] = $pid;
        }
        return (string)$this->uriBuilder->buildUriFromRoute('record_edit', $params);
    }

    public function getInfoUri(int $pageUid, bool $withPreview = false): string
    {
        $params = [
            'id' => $pageUid,
            'tx_easyconf_web_easyconfconfiguration' => [
                'controller' => 'Configuration',
                'action' => 'info',
            ],
        ];
        if ($withPreview) {
            $params['showPreview'] = true;
            $params['popViewId'] = $pageUid;
        }
        return (string)$this->uriBuilder->buildUriFromRoute('web_EasyconfConfiguration', $params);
    }
}
