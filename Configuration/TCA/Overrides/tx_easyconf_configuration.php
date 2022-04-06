<?php

use Buepro\Easyconf\Mapper\MapperFactory;
use Buepro\Easyconf\Utility\TCAUtility;

defined('TYPO3') or die('Access denied.');

(static function() {
    $propertyMaps = [
        TCAUtility::getFieldsToPropertyMap(
            MapperFactory::MAP_ID_TS_CONST,
            'easyconf.demo',
            'company, domain, firstName, lastName'
        ),
        TCAUtility::getFieldsToPropertyMap(
            MapperFactory::MAP_ID_SITE_CONF,
            'easyconf.demo.agency',
            'company, contact, email, phone',
            '',
            'agency'
        ),
    ];
    $palettes = [
        'company' => 'company, domain',
    ];
    $type = [
        'tab.owner' => '--palette--;;company, firstName, lastName',
        'tab.agency' => 'agency_company, agency_contact, agency_email, agency_phone',
    ];
    $tca = &$GLOBALS['TCA']['tx_easyconf_configuration'];
    [$tca['columns'], $tca['palettes'], $tca['types'][0]] = TCAUtility::getConfiguration(
        $propertyMaps,
        $palettes,
        $type,
        'LLL:EXT:easyconf/Resources/Private/Language/locallang_db.xlf'
    );
    unset($tca);
})();
