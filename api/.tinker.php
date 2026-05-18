<?php

return [
    'classes' => [
        'UserRole'    => 'App\Enums\UserRole',
        'RecordStatus' => 'App\Enums\RecordStatus',

        'UserActions' => 'App\Actions\UserActions',
        'RoleActions' => 'App\Actions\RoleActions',

        'UserTableSeeder' => 'Database\Seeders\UserTableSeeder',
        'RoleTableSeeder' => 'Database\Seeders\RoleTableSeeder',
        'CompanyTableSeeder' => 'Database\Seeders\CompanyTableSeeder',
        'BranchTableSeeder' => 'Database\Seeders\BranchTableSeeder',
    ],
];