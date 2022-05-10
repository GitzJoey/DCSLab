<?php

return [
    'DEFAULT' => [
        'API_TOKEN_NAME' => 'api',

        'PAGINATION_LIMIT' => 10,

        'DATA_CACHE' => [
            'ENABLED' => env('DCSLAB_DATACACHE', true),
            'CACHE_TIME' => [
                'ENV' => env('DCSLAB_DATACACHE_TIME', 60),
                '24_HOURS' => 1440,
                '12_HOURS' => 720,
                '6_HOURS' => 360,
                '3_HOURS' => 180,
                '1_HOUR' => 60
            ]
        ],

        'PASSWORD_EXPIRY_DAYS' => 365,

        'ERROR_RETURN_VALUE' => null,

        'KEYWORDS' => [
            'AUTO' => '[AUTO]',
        ]
    ]
];
