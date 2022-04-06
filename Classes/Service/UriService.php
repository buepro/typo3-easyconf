<?php

declare(strict_types=1);

namespace Buepro\Easyconf\Service;

use TYPO3\CMS\Backend\Controller\Event\BeforeFormEnginePageInitializedEvent;
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

    public function getEditUri(int $pageUid, bool $withPreview = false): string
    {
        $params = [
            'edit' => ['tx_easyconf_configuration' => [$pageUid => 'new']],
            'returnUrl' => $this->getInfoUri($pageUid),
        ];
        if ($withPreview) {
            $params['showPreview'] = true;
            $params['popViewId'] = $pageUid;
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
        return (string)$this->uriBuilder->buildUriFromRoute('web_EasyconfConfiguration',$params);
    }
}
