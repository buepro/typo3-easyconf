<?php

declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Buepro\Easyconf\Controller;

use Buepro\Easyconf\Domain\Repository\ConfigurationRepository;
use Buepro\Easyconf\Service\DatabaseService;
use Buepro\Easyconf\Service\UriService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class ConfigurationController extends ActionController
{
    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected ConfigurationRepository $configurationRepository;
    protected PageRepository $pageRepository;
    protected ?int $pageUid;
    protected ?int $templateUid;
    protected ?array $configuration;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        ConfigurationRepository $configurationRepository,
        PageRepository $pageRepository
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->configurationRepository = $configurationRepository;
        $this->pageRepository = $pageRepository;
    }

    public function initializeAction(): void
    {
        parent::initializeAction();
        $databaseService = GeneralUtility::makeInstance(DatabaseService::class);
        $this->pageUid = (int)($this->request->getQueryParams()['id'] ?? 0);
        $this->templateUid = (int)$databaseService->getField('sys_template', 'uid', ['pid' => $this->pageUid]);
        $this->configuration = $databaseService->getRecord('tx_easyconf_configuration', ['pid' => $this->pageUid]);
        if ($this->pageUid > 0 && $this->templateUid > 0 && $this->configuration === null) {
            $this->configuration = $databaseService->addRecord(
                'tx_easyconf_configuration',
                ['pid' => $this->pageUid],
                [Connection::PARAM_INT]
            );
        }
    }

    public function infoAction(): ResponseInterface
    {
        $this->view->assignMultiple([
            'pageUid' => $this->pageUid,
            'templateUid' => $this->templateUid,
            'queryParams' => $this->request->getQueryParams(),
            'sites' => $this->getSitesData(),
        ]);
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        // Adding title, menus, buttons, etc. using $moduleTemplate ...
        $moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($moduleTemplate->renderContent());
    }

    public function editAction(): void
    {
        if ($this->pageUid > 0 && $this->templateUid > 0 && $this->configuration !== null) {
            $this->redirectToUri((new UriService())->getEditUri($this->configuration, false));
        }
        $this->redirect('info');
    }

    protected function getSitesData(): array
    {
        $configurationRepository = $this->configurationRepository;
        $pageRepository = $this->pageRepository;
        $sites = array_map(
            static function (Site $site) use ($configurationRepository, $pageRepository) {
                $configuration = $configurationRepository->getFirstByPid($site->getRootPageId());
                if (($configurationUid = (int)($configuration['uid'] ?? 0)) === 0) {
                    return [];
                }
                $page = $pageRepository->getPage($site->getRootPageId());
                $title = trim($page['title'] ?? $site->getIdentifier());
                $title = $title === '' ? $page['subtitle'] : $title;
                return [
                    'configurationUid' => $configurationUid,
                    'rootPageTitle' => $title
                ];
            },
            GeneralUtility::makeInstance(SiteFinder::class)->getAllSites()
        );
        return array_filter($sites, static fn (array $site): bool => $site !== []);
    }
}
