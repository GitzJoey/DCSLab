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

        $api->assertStatus(401);
    }

    public function test_role_api_call_read_any_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->create();

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.company.read_any', []));

        $api->assertStatus(403);
    }

    public function test_role_api_call_read_ddl_expect_arrays()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->create();

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.admin.role.read.ddl', []));

        $api->assertSuccessful();
    }
}
