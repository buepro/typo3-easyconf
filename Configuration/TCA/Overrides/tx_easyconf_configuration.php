<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

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
            'easyconf.demo.agency',
            'company, contact, email, phone',
            'agency'
        ),
    ];
    $tca['columns'] = TcaUtility::getColumns($propertyMaps, $l10nFile);

    /**
     * Define palettes
     */
    $tca['palettes'] = [
        'palettePompany' => TcaUtility::getPalette(
            'company, domain',
            'owner'
        ),
    ];

    /**
     * Define type
     */
    $tabs = [
        'tabOwner' => implode(', ', [
            '--palette--;;palettePompany',
            TcaUtility::getFieldList('firstName, lastName', 'owner'),
        ]),
        'tabAgency' => TcaUtility::getFieldList('company, contact, email, phone', 'agency'),
    ];
    $tca['types'][0] = TcaUtility::getType($tabs, $l10nFile);

    unset($tca);
})();
