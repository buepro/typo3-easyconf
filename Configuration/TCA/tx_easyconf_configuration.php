<?php

/*
 * This file is part of the composer package buepro/typo3-easyconf.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

defined('TYPO3') or die('Access denied.');

return [
    'ctrl' => [
        'title' => 'LLL:EXT:easyconf/Resources/Private/Language/locallang.xlf:index.title',
        'label' => '',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'enablecolumns' => [
        ],
        'searchFields' => '',
        'iconfile' => 'EXT:easyconf/Resources/Public/Icons/Extension.svg',
        'rootLevel' => 0,
        'hideTable' => 1,
        'EXT' => [
            'easyconf' => [
                'dataHandlerAllowedFields' => '',
            ]
        ],
    ],
];
