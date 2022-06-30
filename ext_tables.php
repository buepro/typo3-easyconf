<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

defined('TYPO3') || die('Access denied.');

(static function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Easyconf',
        'web',
        'Configuration',
        'before:ts',
        [\Buepro\Easyconf\Controller\ConfigurationController::class => 'edit,info'],
        [
            'access' => 'user,group',
            'iconIdentifier' => 'easyconf-extension',
            'labels' => 'LLL:EXT:easyconf/Resources/Private/Language/locallang_module.xlf',
            'navigationComponentId' => 'TYPO3/CMS/Backend/PageTree/PageTreeElement',
            'path' => '/module/web/easyconf/'
        ]
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_easyconf_configuration');
})();
