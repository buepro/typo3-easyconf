<?php

$EM_CONF[$_EXTKEY] = [
    'title'            => 'Easyconf',
    'description'      => 'Provides a module to easily configure main aspects from a website.',
    'category'         => 'module',
    'version'          => '0.1.0',
    'state'            => 'alpha',
    'clearCacheOnLoad' => 1,
    'author'           => 'Roman Büchler',
    'author_email'     => 'rb@buechler.pro',
    'constraints'      => [
        'depends'   => [
            'php'                   => '7.3.0-8.0.99',
            'typo3'                 => '11.5.0-11.5.99',
        ],
        'conflicts' => [],
        'suggests'  => [
            'pizpalue'              => '',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Buepro\\Easyconf\\' => 'Classes'
        ],
    ],
];
