<?php

/** @var array $config */
return [
    'auth' => [
        'username' => 'admin',
        'password' => 'password',
        'encryption' => [
            'key' => '1234567890123456',
        ],
    ],
    'db' => [
        'entity_paths' => [
            __DIR__ . '/src/Db/Entity',
        ],
        'connection' => [
            'driver' => 'pdo_mysql',
            'host' => 'db',
        ],
    ],
];
