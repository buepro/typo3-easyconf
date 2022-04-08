<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use Buepro\Easyconf\Mapper\MapperFactory;
use Buepro\Easyconf\Utility\TCAUtility;

defined('TYPO3') or die('Access denied.');

(static function () {
    $l10nFile = 'LLL:EXT:easyconf/Resources/Private/Language/locallang_db.xlf';
    $tca = &$GLOBALS['TCA']['tx_easyconf_configuration'];

    /**
     * Define columns
     */
    $propertyMaps = [
        TCAUtility::getPropertyMap(
            MapperFactory::MAP_ID_TS_CONST,
            'easyconf.demo',
            'company, domain, firstName, lastName',
            'owner'
        ),
        TCAUtility::getPropertyMap(
            MapperFactory::MAP_ID_SITE_CONF,
            'easyconf.demo.agency',
            'company, contact, email, phone',
            'agency'
        ),
    ];
    $tca['columns'] = TCAUtility::getColumns($propertyMaps, $l10nFile);

    /**
     * Define palettes
     */
    $tca['palettes'] = [
        'palettePompany' => TCAUtility::getPalette(
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
            TCAUtility::getFieldList('firstName, lastName', 'owner'),
        ]),
        'tabAgency' => TCAUtility::getFieldList('company, contact, email, phone', 'agency'),
    ];
    $tca['types'][0] = TCAUtility::getType($tabs, $l10nFile);

    unset($tca);
})();
