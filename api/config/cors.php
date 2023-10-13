<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 
                'sanctum/csrf-cookie', 
                'register', 
                'login', 
                'logout', 
                'forgot-password', 
                'user/two-factor-authentication',
                'user/two-factor-qr-code',
                'user/confirmed-password-status',
            ],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['precognition', 'precognition-success'],

    'max_age' => 0,

    'supports_credentials' => true,

];
