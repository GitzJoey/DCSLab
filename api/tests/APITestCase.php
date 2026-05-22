<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

class APITestCase extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;
}
