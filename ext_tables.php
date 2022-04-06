<?php

defined('TYPO3') || die('Access denied.');

(static function () {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Easyconf',
        'web',
        'Configuration',
        'before:ts',
        [\Buepro\Easyconf\Controller\ConfigurationController::class => 'edit,info'],
        [
            'access' => 'admin',
            'iconIdentifier' => 'easyconf-extension',
            'labels' => 'LLL:EXT:easyconf/Resources/Private/Language/locallang_module.xlf',
            'navigationComponentId' => 'TYPO3/CMS/Backend/PageTree/PageTreeElement',
            'path' => '/module/web/easyconf/'
        ]
    );
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_easyconf_configuration');
})();
