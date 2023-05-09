<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

class APITestCase extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;
}
