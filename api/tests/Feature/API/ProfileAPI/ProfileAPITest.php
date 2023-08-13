<?php

namespace Tests\Feature\API\ProfileAPI;

use App\Enums\UserRoles;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\APITestCase;

class ProfileAPITest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_profile_api_call_update_user_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->create();

        $this->actingAs($user);

        $userArr = User::factory()->make()->toArray();

        $api = $this->json('POST', route('api.post.db.module.profile.update.user'), $userArr);

        $api->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $user->name,
        ]);
    }

    public function test_profile_api_call_update_profile_expect_successful()
    {
        $user = User::factory()
            ->has(Profile::factory())
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
            ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
            ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
            ->create();

        $this->actingAs($user);

        $profileArr = Profile::factory()->make()->toArray();

        $api = $this->json('POST', route('api.post.db.module.profile.update.profile'), $profileArr);

        $api->assertSuccessful();

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'first_name' => $profileArr['first_name'],
            'last_name' => $profileArr['last_name'],
            'address' => $profileArr['address'],
            'city' => $profileArr['city'],
            'postal_code' => $profileArr['postal_code'],
            'country' => $profileArr['country'],
            'tax_id' => $profileArr['tax_id'],
            'ic_num' => $profileArr['ic_num'],
            'remarks' => $profileArr['remarks'],
        ]);
    }

    public function test_profile_api_call_change_password_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->create();

        $this->actingAs($user);

        $password = 'test123';

        $changePasswordArr = [
            'current_password' => $user->password,
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $user['password'] = $password;

        $api = $this->json('POST', route('api.post.db.module.profile.update.password'), $changePasswordArr);

        $api->assertSuccessful();

        $this->assertTrue(Hash::check($password, $user->password));
    }
}
