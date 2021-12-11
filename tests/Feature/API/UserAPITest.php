<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserAPITest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_api_call_require_authentication()
    {
        $api = $this->getJson('/api/get/dashboard/admin/users/read');
        $api->assertStatus(401);

        $api = $this->getJson('/api/get/dashboard/common/ddl/list/statuses');
        $api->assertStatus(401);

    }

    public function test_api_call_authd_read_user()
    {
        $this->assertTrue(true);
    }
}
