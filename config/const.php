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
            '24_HOURS' => 1440,
            '12_HOURS' => 720,
            '6_HOURS' => 360,
            '3_HOURS' => 180,
            '1_HOUR' => 60
        ]
    ],

    'ERROR_RETURN_VALUE' => 0,

    'DROPDOWN' => [
        'ACTIVE_STATUS' => [
            'ACTIVE' => 1,
            'INACTIVE' => 0,
        ],
    ],

    'RANDOMSTRINGRANGE' => [
        'ALPHABET' => ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'],
        'NUMERIC' => [3,4,7,9],
    ]
];
