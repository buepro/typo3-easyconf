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
    protected DatabaseService $databaseService;
    protected ?int $pageUid;
    protected ?int $templateUid;
    protected ?array $configuration;

    protected bool $hidePageNavigation = false;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        ConfigurationRepository $configurationRepository,
        PageRepository $pageRepository,
        DatabaseService $databaseService
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->configurationRepository = $configurationRepository;
        $this->pageRepository = $pageRepository;
        $this->databaseService = $databaseService;
    }

    public function initializeAction(): void
    {
        parent::initializeAction();
        $this->hidePageNavigation = (bool)\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get('easyconf')['hidePageNavigation'] ?? false;
        $this->pageUid = (int)($this->request->getQueryParams()['id'] ?? 0);
        $this->templateUid = intval($this->databaseService->getField('sys_template', 'uid', ['pid' => $this->pageUid]));
        $this->configuration = $this->databaseService->getRecord('tx_easyconf_configuration', ['pid' => $this->pageUid]);
        $this->hiddenPageNavigationHandling();
        if ($this->pageUid > 0 && $this->templateUid > 0 && $this->configuration === null) {
            $this->configuration = self::createConfiguration($this->pageUid);
        }
    }

    public function infoAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->assignMultiple([
            'pageUid' => $this->pageUid,
            'templateUid' => $this->templateUid,
            'queryParams' => $this->request->getQueryParams(),
            'sites' => $this->getSitesData(),
            'agency' => $this->getAgencyData(),
            'isAdmin' => $GLOBALS['BE_USER']->isAdmin(),
        ]);
        return $moduleTemplate->renderResponse('Info');
    }

    public function editAction(): ResponseInterface
    {
        if ($this->pageUid > 0 && $this->templateUid > 0 && $this->configuration !== null) {
            return $this->redirectToUri((new UriService())->getEditUri($this->configuration, false));
        }
        return $this->redirect('info');
    }

    protected function getSitesData(): array
    {
        $configurationRepository = $this->configurationRepository;
        $pageRepository = $this->pageRepository;
        $hidePageNavigation = $this->hidePageNavigation;

        $sites = array_map(
            static function (Site $site) use ($configurationRepository, $pageRepository, $hidePageNavigation) {
                $configuration = $configurationRepository->getFirstByPid($site->getRootPageId());
                $configurationUid = (int)($configuration['uid'] ?? 0);
                if ($configurationUid === 0) {
                    if($hidePageNavigation) {
                        $configurationUid = (int)self::createConfiguration($site->getRootPageId())['uid'];
                    } else {
                        return [];
                    }
                }
                if(!$GLOBALS['BE_USER']->isInWebMount($site->getRootPageId())) {
                    return [];
                }
                $page = $pageRepository->getPage($site->getRootPageId());
                $title = trim($page['title'] ?? $site->getIdentifier());
                $title = $title === '' ? $page['subtitle'] : $title;
                return [
                    'configurationUid' => $configurationUid,
                    'rootPageTitle' => $title,
                    'rootPageUid' => $site->getRootPageId(),
                ];
            },
            GeneralUtility::makeInstance(SiteFinder::class)->getAllSites()
        );
        return array_filter($sites, static fn (array $site): bool => $site !== []);
    }

    public static function createConfiguration(int $pid)
    {
        return GeneralUtility::makeInstance(DatabaseService::class)
            ->addRecord(
                'tx_easyconf_configuration',
                ['pid' => $pid],
                [Connection::PARAM_INT]
            );
    }

    /**
     * Hidden page navigation
     * If multiple pages accessible with root TS template, redirect to Info view
     * If one page accessible, set this as the active one
     * @return void
     */
    protected function hiddenPageNavigationHandling(): void
    {
        if($this->hidePageNavigation) {
            $rootPageUids = array_column($this->getSitesData(), 'rootPageUid');
            if($rootPageUids && !($this->templateUid && in_array($this->pageUid, $rootPageUids))) {
                // if multiple pages with TS root accessible
                if(count($rootPageUids) > 1) {
                    $this->redirect('info');
                } elseif (count($rootPageUids) === 1) {
                    $this->pageUid = $rootPageUids[0];
                    $this->templateUid = intval($this->databaseService->getField('sys_template', 'uid', ['pid' => $this->pageUid]));
                    $this->configuration = $this->databaseService->getRecord('tx_easyconf_configuration', ['pid' => $this->pageUid]);
                }
            }
        }
    }

    protected function getAgencyData(): array
    {
        $agencyData = $this->getAgencyDataFromSite() ?? $this->settings['agency'] ?? null;
        if ($agencyData === null) {
            return [];
        }
        if (isset($agencyData['phone'])) {
            $agencyData['phone'] = preg_replace('#(\s|\.|\/|-|\(|\))#i', '', $agencyData['phone']) ?? '';
        }
        return $agencyData;
    }

    private function getAgencyDataFromSite(): ?array
    {
        $site = $this->request->getAttribute('site');
        if (
            $site instanceof Site &&
            is_array($settings = $site->getConfiguration()['easyconf'] ?? false) &&
            is_array($agency = $settings['data']['admin']['agency'] ?? false)
        ) {
            return $agency;
        }
        return null;
    }
}
