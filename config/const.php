<?php

return [
    'DEFAULT' => [
        'API_TOKEN_NAME' => 'api',

        'PAGINATION_LIMIT' => 10,

        'DATA_CACHE' => [
            'ENABLED' => env('DCSLAB_DATACACHE', true),
            'CACHE_TIME' => [
                'ENV' => env('DCSLAB_DATACACHE_TIME', 3600),
                '24_HOURS' => 86400,
                '12_HOURS' => 43200,
                '6_HOURS' => 21600,
                '3_HOURS' => 10800,
                '1_HOUR' => 3600
            ]
        ],

        'PASSWORD_EXPIRY_DAYS' => 365,

        'ERROR_RETURN_VALUE' => null,

        'KEYWORDS' => [
            'AUTO' => '[AUTO]',
        ]
    ]
];
