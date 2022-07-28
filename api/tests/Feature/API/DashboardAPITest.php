<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\APITestCase;

class DashboardAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_dashboard_api_get_usermenu_expect_array()
    {
        $this->markTestSkipped('Under Constructions');
    }
}
