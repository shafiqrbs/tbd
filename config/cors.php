<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',
        'http://127.0.0.1:5173',
        'http://www.tbd.local',
        'https://health.poskeeper.com',
        'https://hms.tbhsd.gov.bd',
        'https://sandra.poskeeper.com',
        'https://pos.poskeeper.com'

    ],


    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
