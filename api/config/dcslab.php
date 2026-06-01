<?php

return [
    'API_TOKEN_NAME' => 'api',

    'PAGINATION_LIMIT' => 10,

    'DATA_CACHE' => [
        'ENABLED' => env('DCSLAB_DATACACHE', true),
        'CACHE_TIME' => env('DCSLAB_DATACACHE_TIME', 3600),
        'LOGS_CHANNEL_NAME' => env('DCSLAB_DATACACHE_LOG_CHANNEL', 'cachehits'),
    ],

    'PASSWORD_EXPIRY_DAYS' => 90,

    'ERROR_RETURN_VALUE' => null,

    'KEYWORDS' => [
        'AUTO' => '_AUTO_',
    ],
];
