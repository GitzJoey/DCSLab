<?php

namespace Tests\Feature\API\RoleAPI;

use App\Enums\UserRoles;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class RoleAPICreateTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_role_api_call_store_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->create();

        $roleArr = Role::factory()->make()->toArray();

        $api = $this->json('POST', route('api.post.db.company.role.save'), $roleArr);

        $api->assertStatus(401);
    }

    public function test_role_api_call_store_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $roleArr = Role::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.role.save'), $roleArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('rolees', [
            'company_id' => $company->id,
            'code' => $roleArr['code'],
            'name' => $roleArr['name'],
        ]);
    }
}
