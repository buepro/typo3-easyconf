<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Buepro\Easyconf\Mapper\EasyconfMapper;
use Buepro\Easyconf\Mapper\SiteConfigurationMapper;
use Buepro\Easyconf\Mapper\TypoScriptConstantMapper;
use Buepro\Easyconf\Utility\TcaUtility;

defined('TYPO3') or die('Access denied.');

(static function () {
    $l10nFile = 'LLL:EXT:easyconf/Resources/Private/Language/locallang_db.xlf';
    $tca = &$GLOBALS['TCA']['tx_easyconf_configuration'];

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
     * Define type
     */
    $tabs = [
        'tabTypoScript' => implode(', ', [
            '--palette--;;paletteCompany',
            TcaUtility::getFieldList('firstName, lastName', 'owner'),
        ]),
        'tabSiteConfiguration' => TcaUtility::getFieldList('company, contact, email, phone', 'agency'),
        'tabEasyconf' => TcaUtility::getFieldList('showAllProperties', 'easyconf'),
    ];
    $tca['types'][0] = TcaUtility::getType($tabs, $l10nFile);

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

    unset($tca);
})();
