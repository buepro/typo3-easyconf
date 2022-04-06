<?php

namespace Buepro\Easyconf\Controller;

use Buepro\Easyconf\Service\DatabaseService;
use Buepro\Easyconf\Service\UriService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class ConfigurationController extends ActionController
{
    /** @var ModuleTemplateFactory  */
    protected $moduleTemplateFactory;
    /** @var ?int $pageUid */
    protected $pageUid;
    /** @var ?int $templateUid */
    protected $templateUid;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
    }

    public function initializeAction()
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
