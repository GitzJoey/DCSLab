# Installation

- Install latest Laravel

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
    ```

- Update README.md
    Change to only contains
    1. Title is DCSLab
    2. Description is DCSLab - Laravel API

- Setting .env
    1. Copy .env to .env.example
    2. Set App
        ```
        APP_NAME=DCSLab
        APP_URL=https://dcslab-api.gitzjoey.online
        ```
    3. Set Sanctum
        ```
        SANCTUM_STATEFUL_DOMAINS=dcslab.gitzjoey.online
        ```
    4. Set MySQL for default DB
        ```
        DB_CONNECTION=mysql
        DB_HOST=dcslab-mysql
        DB_PORT=3306
        DB_DATABASE=dcslab
        DB_USERNAME=dcslab
        DB_PASSWORD=password
        ```
    5. Set Mail
        ```
        MAIL_FROM_ADDRESS="hello@dcslab.com"
        ```

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
    4. Edit app.php in bootstrap folder
        a. Add this
            ```
            $middleware->redirectTo(
                guests: '/login',
                users: fn () => ''
            );
            ```
        b. Add ->prefersJsonResponses() before create()
    5. Edit api.php
        a. Remove all routes for now. Keep the all the "use"

- Vendor installation
    1. Fortify
        a. Publish
            ```
            php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"
            ```
        b. Set bootstrap/providers.php
            ```
            use App\Providers\FortifyServiceProvider;

            return [
                FortifyServiceProvider::class,
            ];
            ```
    2. Lang
        a. Publish
            ```
            php artisan lang:publish
            ```
        b. Create folder "id" for Indonesian language
        c. Translate all files in "en" folder and put in "id" folder
    3. CORS
        a. Publish
            ```
            php artisan config:publish cors
            ```
        b. Edit cors.php
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
    2. Laratrust
        a. Publish
            ```
            php artisan vendor:publish --tag="laratrust"
            php artisan vendor:publish --tag="laratrust-seeder"
            ```
        b. Edit laratrust_seeder.php
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
    3. Hashids
        a. Publish
            ```
            php artisan vendor:publish --provider="Vinkla\Hashids\HashidsServiceProvider"
            ```
        b. Edit hashids.php
            ```
            'main' => [
                'salt' => 'dcslab',
                'length' => 24,
            ],

            'alternative' => [
                'salt' => 'gitzjoey',
                'length' => 24,
            ],
            ```

- Some config settings
    1. logging.php
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
    2. Create new config called dcslab.php
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
    3. Create ziggy.php
        Contains empty like below
        ```
        <?php
        ```
    4. Create pint.json
        ```
        {
            "preset": "laravel",
            "exclude": [

            ],
            "notPath": [
                "config/laratrust.php",
                "config/laratrust_seeder.php",
                "database/seeders/LaratrustSeeder.php"
            ]
        }
        ```
    5. Create .tinker.php
        ```
        <?php

        return [
            'classes' => [
                'UserRole'    => 'App\Enums\UserRole',
                'RecordStatus' => 'App\Enums\RecordStatus',

                'DashboardActions' => 'App\Actions\Dashboard\DashboardActions',
                'UserActions' => 'App\Actions\UserActions',
                'RoleActions' => 'App\Actions\RoleActions',

                'UserTableSeeder' => 'Database\Seeders\UserTableSeeder',
                'RoleTableSeeder' => 'Database\Seeders\RoleTableSeeder',
                'CompanyTableSeeder' => 'Database\Seeders\CompanyTableSeeder',
                'BranchTableSeeder' => 'Database\Seeders\BranchTableSeeder',        
            ],
        ];

        ```

- Configure tests folder
    a. Remove ExampleTest.php (in tests/Feature and tests/Unit)
    b. In tests folder (root)
        1. Create ApiTestCase
            - Extends TestCase.php
            - Use RefreshDatabase
        2. Create ActionsTestCase
            - Extends TestCase.php
            - Use RefreshDatabase
    c. Update composer.json
        Add this 2
        ```
        "Tests\\Unit\\Actions\\": "tests/Unit/Actions/",
        "Tests\\Feature\\API\\": "tests/Feature/API/"
        ```

- Remove unused
    1. Remove database.sqlite
