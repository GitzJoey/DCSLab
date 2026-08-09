# Installation

- Run command in api directory to install latest Laravel

- Install required packages
    ```
    composer require laravel/fortify santigarcor/laratrust tightenco/ziggy vinkla/hashids
    ```

- Update composer.json
    Follow the below text
    ```
    "name": "gitzjoey/dcslab",
    "type": "project",
    "description": "DCSLab - Laravel Always Updated ERP System",
    "keywords": ["ERP", "framework", "laravel", "gitzjoey"],

    ...

    "psr-4": {
        "Tests\\": "tests/",
        "Tests\\Unit\\Actions\\": "tests/Unit/Actions/",
        "Tests\\Feature\\API\\": "tests/Feature/API/"
    }
    ```

- Update README.md
    Change to only contains
    1. Title is DCSLab
    2. Description is DCSLab - Laravel API

- Change into API project    
    1. Run artisan command to make the laravel is api project
        ```
        php artisan install:api
        ```
    2. Make sure below file is deleted
        - vite.config.js
    3. Make sure below folders is deleted
        - folder css inside resources
        - folder js inside resources
    3. For welcome.blade.php
        Replace it into empty html template with
            a. <title> is "DCSLab"
            b. <body> only contains "DCSLab API Is Running..."

- Do the vendor installation
    1. Fortify
        ```
        php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"
        ```
    2. Laratrust
        ```
        php artisan vendor:publish --tag="laratrust"
        php artisan vendor:publish --tag="laratrust-seeder"

        php artisan laratrust:setup 
        php artisan laratrust:seeder 
        ```        
    3. Lang
        ```
        php artisan lang:publish
        ```
    4. CORS
        ```
        php artisan config:publish cors
        ```

- Bootstrap settings
    1. app.php
        Add below
        ```
        $middleware->statefulApi();

        $middleware->alias([
            'precognitive' => HandlePrecognitiveRequests::class,
            'validate.user' => ValidateUser::class,
        ]);

        $middleware->redirectTo(
            guests: '/login',
            users: fn () => ''
        );
        ```
    2. provider.php
        ```
        use App\Providers\AppServiceProvider;
        use App\Providers\FortifyServiceProvider;

        return [
            AppServiceProvider::class,
            FortifyServiceProvider::class,
        ];
        ```

- Config settings
    1. cors.php
        ```
        'paths' => ['api/*',
                    'sanctum/csrf-cookie',
                    'register',
                    'login',
                    'logout',
                    'forgot-password',
                    'user/confirmed-password-status',
                    'user/confirm-password',
                    'user/two-factor-authentication',
                    'user/confirmed-two-factor-authentication',
                    'user/two-factor-qr-code',
                    'user/two-factor-recovery-codes',
                    'user/two-factor-secret-key',
                    'two-factor-challenge',
        ],


        'exposed_headers' => ['precognition', 'precognition-success'],

        'supports_credentials' => true,
        ```
    2.laratrust_seeder.php
        Copy below
        ```
        'roles_structure' => [
            'developer' => [
                //Bypass all permissions
            ],
            'administrator' => [
                'user' => 'c,r,ra,u,ac,au',
                'profile' => 'r,u',
                'company' => 'c,r,ra,u,d,rs,ac,au,ad,ars',
                'branch' => 'c,r,ra,u,d,rs,ac,au,ad,ars',
            ],
            'user' => [
                'profile' => 'r,u',
            ],

            /* #region Extensions */
            'POS-owner' => [
                'company' => 'c,r,ra,u,d,rs,ac,au,ad,ars',
                'branch' => 'c,r,ra,u,d,rs,ac,au,ad,ars',
            ],
            /* #endregion */
        ],

        'permissions_map' => [
            'c' => 'create',
            'r' => 'read',
            'ra' => 'readAny',
            'u' => 'update',
            'd' => 'delete',

            'rs' => 'restore',

            'ac' => 'authorizeCreate',
            'au' => 'authorizeUpdate',
            'ad' => 'authorizeDelete',

            'ars' => 'authorizeRestore',
        ],
        ```
    3. logging.php
        Add this
        ```
        'cachehits' => [
            'driver' => 'single',
            'path' => storage_path('logs/cachehits.log'),
            'level' => 'info',
        ],

        'perfs' => [
            'driver' => 'single',
            'path' => storage_path('logs/performances.log'),
            'level' => 'info',
        ],
        ```
    4. Create new config called dcslab.php
        ```
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
        ```
    5. Create ziggy.php
        Contains empty like below
        ```
        <?php
        ```