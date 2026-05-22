<?php

namespace Tests\Feature\API\RoleAPI;

use App\Enums\UserRoles;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;

class RoleAPIReadTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_role_api_call_read_any_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->create();

        $api = $this->getJson(route('api.get.db.admin.role.read_any', []));

        $api->assertUnauthorized();
    }

    public function test_role_api_call_read_any_without_access_right_expect_unauthorized_message()
    {
        $this->markTestSkipped('Test under construction');

        $user = User::factory()
            ->create();

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.admin.role.read_any', []));

        $api->assertForbidden();
    }

    public function test_role_api_call_read_any_expect_collection()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->create();

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.admin.role.read_any', []));

        $api->assertSuccessful();
    }
}
