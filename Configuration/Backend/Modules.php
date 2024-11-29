<?php declare(strict_types=1);

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Buepro\Easyconf\Controller\ConfigurationController;

$hideNavigation = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get('easyconf')['hidePageNavigation'] ?? false;

return [
    'web_Easyconf' => [
        'parent' => 'site',
        'position' => ['before' => 'site_configuration'],
        'access' => 'user',
        'workspaces' => 'live',
        'iconIdentifier' => 'easyconf-extension',
        'path' => '/module/web/easyconf',
        'labels' => 'LLL:EXT:easyconf/Resources/Private/Language/locallang_module.xlf',
        'extensionName' => 'Easyconf',
        'controllerActions' => [
            ConfigurationController::class => ['edit', 'info'],
        ],
        'navigationComponent' => $hideNavigation ? '' : '@typo3/backend/page-tree/page-tree-element',
    ],
];
