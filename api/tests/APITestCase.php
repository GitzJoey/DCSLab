<?php

namespace Tests;

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
                '--seed' => true,
            ]);
        }
    }
}
