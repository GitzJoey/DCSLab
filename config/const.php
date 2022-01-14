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

        'KEYWORDS' => [
            'AUTO' => '[AUTO]',
        ]
    ],

    'ENUMS' => [
        'ACTIVE_STATUS' => [
            'ACTIVE' => 1,
            'INACTIVE' => 0
        ],
        'PRODUCT_TYPE' => [
            'RAW_MATERIAL' => 1,
            'WORK_IN_PROGRESS' => 2,
            'FINISHED_GOODS' => 3,
            'SERVICE' => 4
        ],
        'PAYMENT_TERM' => [
            'PAYMENT_IN_ADVANCE' => 'PIA',
            '30DAYS_AFTER_INVOICE' => 'NET30',
            'END_OF_MONTH' => 'EOM',
            'CASH_ON_DELIVERY' => 'COD',
            'CASH_ON_NEXT_DELIVERY' => 'CND',
            'CASH_BEFORE_SHIPMENT' => 'CBS'
        ],
        'UNIT_CATEGORY' => [
            'PRODUCTS' => 1,
            'SERVICES' => 2,
            'PRODUCTS_AND_SERVICES' => 3
        ]
    ],
];
