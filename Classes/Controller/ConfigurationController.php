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
        $this->templateUid = intval($databaseService->getField('sys_template', 'uid', ['pid' => $this->pageUid]));
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
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->assignMultiple([
            'pageUid' => $this->pageUid,
            'templateUid' => $this->templateUid,
            'queryParams' => $this->request->getQueryParams(),
            'sites' => $this->getSitesData(),
            'agency' => $this->getAgencyData(),
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
            is_array($settings = $site->getAttribute('easyconf')) &&
            is_array($agency = $settings['data']['admin']['agency'] ?? false)
        ) {
            return $agency;
        }
        return null;
    }
}
