<?php

/** @var array $config */
return [
    'auth' => [
        'admin' => [
            'username' => 'admin',
            'password' => 'password',
        ],
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
        'models' => [
            PHNTM . 'src/Model/' => 'Phntm\Lib\Model',
        ],
        'connection' => [
            'driver' => 'pdo_mysql',
            'host' => 'db',
        ],
    ],
    'images' => [
        'load_from' => [
            PHNTM . '/images',
        ],
        'source' => ROOT . '/images',
        'distribute' => ROOT . '/public/images',
    ],
    'routing' => [
        'cache' => [
            'enabled' => true,
            'key' => 'phntm.lib.routing.cache',
            'ttl' => 3600,
        ],
    ],
    'view' => [
        'load_from' => [
            ROOT . PHNTM . 'views',
        ],
    ],
    'site' => [
        'logo' => 'images/logo.png',
    ],
];
