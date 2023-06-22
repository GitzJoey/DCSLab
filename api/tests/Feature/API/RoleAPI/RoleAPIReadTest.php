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
