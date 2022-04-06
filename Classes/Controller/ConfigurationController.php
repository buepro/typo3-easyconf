<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Controller;

use Buepro\Easyconf\Service\DatabaseService;
use Buepro\Easyconf\Service\UriService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class ConfigurationController extends ActionController
{
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected ?int $pageUid;
    protected ?int $templateUid;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
    }

    public function initializeAction(): void
    {
        parent::initializeAction();
        $databaseService = GeneralUtility::makeInstance(DatabaseService::class);
        $this->pageUid = (int)($this->request->getQueryParams()['id'] ?? 0);
        $this->templateUid = (int)$databaseService->getField('sys_template', 'uid', ['pid' => $this->pageUid]);
    }

    public function infoAction(): ResponseInterface
    {
        $this->view->assignMultiple([
            'pageUid' => $this->pageUid,
            'templateUid' => $this->templateUid,
            'queryParams' => $this->request->getQueryParams(),
        ]);
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        // Adding title, menus, buttons, etc. using $moduleTemplate ...
        $moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    public function editAction(): void
    {
        if ($this->pageUid > 0 && $this->templateUid > 0) {
            $this->redirectToUri((new UriService())->getEditUri($this->pageUid, false));
        }
        $this->redirect('info');
    }
}
