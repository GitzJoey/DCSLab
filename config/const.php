<?php

return [
    'DEFAULT' => [
        'ROLE' => [
            'DEV' => 'dev',
            'ADMIN' => 'administrator',
            'USER' => 'user'
        ],

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

        'RANDOMSTRINGRANGE' => [
            'ALPHABET' => ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'],
            'NUMERIC' => [3,4,7,9],
        ]
    ],

    'RULES' => [
        'VALID_DROPDOWN_VALUES' => [
            'ACTIVE_STATUS' => [
                'ACTIVE' => 1,
                'INACTIVE' => 0,
            ],
        ],
    ],
];
