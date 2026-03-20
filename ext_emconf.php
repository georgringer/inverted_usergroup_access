<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Inverted Usergroup Access',
    'description' => 'Provides the possibility to negate the usergroup permissions',
    'category' => 'be',
    'author' => 'Georg Ringer',
    'author_email' => 'mail@ringer.it',
    'state' => 'alpha',
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.40-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'classmap' => ['Classes'],
    ],
];
