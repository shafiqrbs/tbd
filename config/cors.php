<?php

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [
        'https://health.poskeeper.com',
        'https://hms.tbhsd.gov.bd',
        'https://sandra.poskeeper.com',
        'https://pos.poskeeper.com',
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'X-Api-Key',
        'Accept',
        'Origin',
    ],

    'exposed_headers' => [],

    'max_age' => 3600,

    'supports_credentials' => false, // IMPORTANT for JWT
];
