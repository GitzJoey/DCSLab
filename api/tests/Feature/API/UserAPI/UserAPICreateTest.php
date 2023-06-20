<?php

namespace Tests\Feature\API\UserAPI;

use App\Enums\UserRoles;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class UserAPICreateTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_user_api_call_store_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->create();

        $userArr = User::factory()->make()->toArray();
        $userArr = array_merge($userArr, Profile::factory()->make()->toArray());

        $role = Hashids::encode(Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);
        $userArr['roles'][0] = $role;

        $api = $this->json('POST', route('api.post.db.admin.users.save'), $userArr);

        $api->assertStatus(401);
    }

    public function test_user_api_call_store_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->create();

        $this->actingAs($user);

        $userArr = User::factory()->make()->toArray();
        $userArr = array_merge($userArr, Profile::factory()->make()->toArray());

        $role = Hashids::encode(Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);
        $userArr['roles'][0] = $role;

        $api = $this->json('POST', route('api.post.db.admin.users.save'), $userArr);

        $api->assertStatus(403);
    }

    public function test_user_api_call_store_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->create();

        $this->actingAs($user);

        $userArr = User::factory()->make()->toArray();
        $userArr = array_merge($userArr, Profile::factory()->make()->toArray());

        $role = Hashids::encode(Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);
        $userArr['roles'][0] = $role;

        $api = $this->json('POST', route('api.post.db.admin.users.save'), $userArr);

        $api->assertSuccessful();
    }

    public function test_user_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->create();

        $this->actingAs($user);

        $userArr = [];

        $api = $this->json('POST', route('api.post.db.admin.users.save'), $userArr);

        $api->assertJsonValidationErrors([
            'name',
            'email',
            'roles',
            'tax_id',
            'ic_num',
            'status',
            'country',
        ]);
    }
}
