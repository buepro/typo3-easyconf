<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Buepro\Easyconf\Mapper\EasyconfMapper;
use Buepro\Easyconf\Mapper\SiteConfigurationMapper;
use Buepro\Easyconf\Mapper\SiteSettingsMapper;
use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;
use Buepro\Easyconf\Utility\TcaUtility;

defined('TYPO3') or die('Access denied.');

(static function () {
    if(\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get('easyconf')['loadDemoConfiguration'] ?? false) {
        $l10nFile = 'LLL:EXT:easyconf/Resources/Private/Language/locallang_db.xlf';
        $tca = &$GLOBALS['TCA']['tx_easyconf_configuration'];
        $tca['ctrl']['type'] = 'group';

        /**
         * Define columns
         */
        $propertyMaps = [
            TcaUtility::getPropertyMap(
                TypoScriptConstantMapper::class,
                'easyconf.demo',
                'company, domain, firstName, lastName',
                'owner'
            ),
            TcaUtility::getPropertyMap(
                SiteSettingsMapper::class,
                'features',
                'newsletter.popup, heroImage, slider, specialOffer',
                'settings'
            ),
            TcaUtility::getPropertyMap(
                SiteConfigurationMapper::class,
                'easyconf.data.demo',
                'company, contact, email, phone',
                'agency'
            ),
            TcaUtility::getPropertyMap(
                EasyconfMapper::class,
                'demo',
                'showAllProperties',
                'easyconf'
            ),
            TcaUtility::getPropertyMap(
                EasyconfMapper::class,
                'group',
                'group',
                ''
            )
        ];
        $tca['columns'] = TcaUtility::getColumns($propertyMaps, $l10nFile);

        /**
         * Define palettes
         */
        $tca['palettes'] = [
            'paletteCompany' => TcaUtility::getPalette(
                'company, domain',
                'owner'
            ),
        ];

        /**
         * Define types for groups of tabs
         */
        $tabs = [
            'tabTypoScript' => implode(', ', ['--palette--;;paletteCompany',
                TcaUtility::getFieldList('firstName, lastName', 'owner'),
            ]),
            'tabSiteConfiguration' => TcaUtility::getFieldList('company, contact, email, phone', 'agency'),
            'tabEasyconf' => TcaUtility::getFieldList('showAllProperties', 'easyconf'),
        ];
        TcaUtility::addType('0', $tabs, $l10nFile, 'icon:module-list');

        TcaUtility::addType('1',
            ['tabSiteSettings' => TcaUtility::getFieldList('newsletter.popup, heroImage, slider, specialOffer', 'settings')],
            $l10nFile,
            'EXT:easyconf/Resources/Public/Icons/CardToggle.svg');



        /**
         * Modify columns
         */
        TcaUtility::modifyColumns(
            $tca['columns'],
            'showAllProperties',
            [
                'onChange' => 'reload',
                'config' => ['type' => 'check', 'renderType' => 'checkboxToggle'],
            ],
            'easyconf'
        );
        TcaUtility::modifyColumns(
            $tca['columns'],
            'firstName, lastName',
            ['displayCond' => 'FIELD:easyconf_show_all_properties:REQ:true'],
            'owner'
        );
        TcaUtility::modifyColumns(
            $tca['columns'],
            'newsletter.popup, heroImage, slider, specialOffer',
            [
                'config' => ['type' => 'check', 'renderType' => 'checkboxToggle'],
            ],
            'settings'
        );
    }

    unset($tca);
})();
