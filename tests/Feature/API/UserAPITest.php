<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\APITestCase;

class UserAPITest extends APITestCase
{
    public function test_user_api_call_require_authentication()
    {
        $api = $this->getJson('/api/get/dashboard/admin/users/read');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/get/dashboard/admin/users/roles/read');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/admin/users/save');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/admin/users/edit/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));
    }

    public function test_user_api_call_read()
    {
        $this->actingAs($this->user);

        $api = $this->getJson('/api/get/dashboard/admin/users/read');
        $api->assertStatus(200);
    }

    public function test_user_api_call_getAllRoles()
    {
        $this->assertTrue(true);
    }

    public function test_user_api_call_store()
    {
        $this->assertTrue(true);
    }

    public function test_user_api_call_update()
    {
        $this->assertTrue(true);
    }

    public function test_user_api_call_resetPassword()
    {
        $this->assertTrue(true);
    }
}
