<?php

namespace Tests;

use Illuminate\Support\Facades\File;

class ActionTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (!file_exists(database_path('database.sqlite'))) {
            File::put(database_path('database.sqlite'), null);

            $this->artisan('migrate');
            $this->artisan('db:seed');
        }
    }
}
