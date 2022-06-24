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
        $this->actingAs($this->developer);

        $api = $this->getJson('/api/get/dashboard/admin/users/read?search=&paginate=false');
        $api->assertStatus(200);
    }

    public function test_user_api_call_getAllRoles()
    {
        $this->markTestSkipped('Under Construction');
    }

    public function test_user_api_call_store()
    {
        $this->markTestSkipped('Under Construction');
    }

    public function test_user_api_call_update()
    {
        $this->markTestSkipped('Under Construction');
    }

    public function test_user_api_call_resetPassword()
    {
        $this->markTestSkipped('Under Construction');
    }
}
