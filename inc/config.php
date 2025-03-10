<?php

/** @var array $config */
return [
    'auth' => [
        'username' => 'admin',
        'password' => 'password',
        'encryption' => [
            'key' => '1234567890123456',
        ],
        'providers' => [
            'google' => [
                'enabled' => false,
                'client_id' => 'your-client-id',
                'client_secret' => 'your-client-secret',
                'redirect_uri' => 'http://localhost:8080/auth/google/callback',
            ],
        ]
    ],
    'db' => [
        'entity_paths' => [
            __DIR__ . '/src/Db/Entity',
        ],
        'models' => [
            __DIR__ . '/src/Model/' => 'Phntm\Lib\Model',
        ],
        'connection' => [
            'driver' => 'pdo_mysql',
            'host' => 'db',
        ],
    ],
    'images' => [
        'source' => ROOT . '/images',
        'distribute' => ROOT . '/public/images',
    ],
];
