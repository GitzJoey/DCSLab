<?php

namespace Tests;

use App\Enums\UserRoles;
use App\Models\User;

use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\UserTableSeeder;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class APITestCase extends TestCase
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
            $seed_user->callWith(UserTableSeeder::class, [false, 1, UserRoles::DEVELOPER]);

            $seed_company = new CompanyTableSeeder();
            $seed_company->callWith(CompanyTableSeeder::class, [2]);
        }

        $this->user = User::whereRelation('roles', 'name', '=', UserRoles::USER->value)->whereHas('companies')->inRandomOrder()->first();
        $this->developer = User::whereRelation('roles', 'name', '=', UserRoles::DEVELOPER->value)->first();
    }
} 