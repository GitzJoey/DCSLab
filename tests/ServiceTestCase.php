<?php

namespace Tests;

use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\UserTableSeeder;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ServiceTestCase extends TestCase
{
    protected function setUp(): void
    {
        Parent::setUp();
        
        if (!file_exists(database_path('database.sqlite'))) {
            File::put(database_path('database.sqlite'), null);

            $this->artisan('migrate', [
                '--env' => 'testing',
                '--path' => 'database/migrations/testdb',
                '--seed' => true
            ]);

            $seed_user = new UserTableSeeder();
            $seed_user->callWith(UserTableSeeder::class, [false, 1]);

            $seed_company = new CompanyTableSeeder();
            $seed_company->callWith(CompanyTableSeeder::class, [2]);
        }        
    }
} 