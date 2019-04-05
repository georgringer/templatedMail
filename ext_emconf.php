<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Templated emails',
    'description' => '',
    'category' => 'be',
    'author' => 'Georg Ringer',
    'author_email' => '',
    'state' => 'beta',
    'clearCacheOnLoad' => 1,
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.6-10.9.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'GeorgRinger\\TemplatedMail\\' => 'Classes'
        ]
    ],
];
