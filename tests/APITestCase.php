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
        parent::setUp();

        if (! file_exists(database_path('database.sqlite'))) {
            File::put(database_path('database.sqlite'), null);

            $this->artisan('migrate', [
                '--env' => 'testing',
                '--seed' => true,
            ]);
        }
    }
}
