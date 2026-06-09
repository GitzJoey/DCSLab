<?php

namespace Tests\Feature\API\ProfileAPI;

use App\Models\User;
use App\Enums\RecordStatus;
use Illuminate\Support\Facades\Hash;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class EditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

        protected function createUserWithRole(): User
    {
        return User::factory()
            ->setNotRequiredResetPassword()
            ->has(Profile::factory()->setStatusActive())
            ->hasAttached(Role::where('name', '=', UserRole::DEVELOPER->value)->first())
            ->create();
    }

    protected function createUserWithRoleSettings(): User
    {
        return User::factory()
            ->setNotRequiredResetPassword()
            ->has(Profile::factory()->setStatusActive())
            ->hasAttached(Role::where('name', '=', UserRole::DEVELOPER->value)->first())
            ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
            ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
            ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
            ->create();
    }

    public function test_profile_api_call_update_user_profile_expect_successful()
    {
        $user = $this->createUserWithRole();

        $this->actingAs($user);

        $userArr = User::factory()->make()->toArray();

        $api = $this->json('PATCH', route('api.dashboard.profile.update.user_profile'), $userArr);

        $api->assertSuccessful();

        $api->assertJsonFragment([
            'data' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $userArr['name'],
        ]);
    }

    public function test_profile_api_call_update_personal_info_expect_successful()
    {
        $user = $this->createUserWithRoleSettings();

        $this->actingAs($user);

        $profileArr = Profile::factory()->make()->toArray();

        $api = $this->json('PATCH', route('api.dashboard.profile.update.personal_info'), $profileArr);

        $api->assertSuccessful();

        $api->assertJsonFragment([
            'data' => true,
        ]);

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
        $user = $this->createUserWithRole();

        $this->actingAs($user);

        $default_user_factory_password = 'password';
        $password = 'test123';

        $changePasswordArr = [
            'current_password' => $default_user_factory_password,
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $api = $this->json('PATCH', route('api.dashboard.profile.update.password'), $changePasswordArr);

        $api->assertSuccessful();

        $this->assertTrue(Hash::check($password, $user->fresh()->password));
    }

    public function test_profile_api_call_update_account_settings_expect_successful()
    {
        $user = $this->createUserWithRoleSettings();

        $this->actingAs($user);

        $settingsArr = [
            'theme' => 'test_theme',
            'date_format' => 'yyyy-MMM-dd',
            'time_format' => 'hh:mm:ss',
        ];

        $api = $this->json('PATCH', route('api.dashboard.profile.update.account_settings'), $settingsArr);

        $api->assertSuccessful();

        $dbMapping = [
            'theme' => 'PREFS.THEME',
            'date_format' => 'PREFS.DATE_FORMAT',
            'time_format' => 'PREFS.TIME_FORMAT',
        ];

        foreach ($settingsArr as $key => $value) {
            $this->assertDatabaseHas('settings', [
                'user_id' => $user->id,
                'key' => $dbMapping[$key],
                'value' => $value,
            ]);
        }
    }

    public function test_profile_api_call_update_roles_expect_successful()
    {
        $user = $this->createUserWithRoleSettings();
        
        Role::factory()->count(3)->create();

        $this->actingAs($user);

        $rolesArr['roles'] = [
            Role::inRandomOrder()->first()->id,
        ];

        $api = $this->json('PATCH', route('api.dashboard.profile.update.roles'), $rolesArr);

        $api->assertSuccessful();

        $this->assertDatabaseHas('role_user', [
            'user_id' => $user->id,
            'role_id' => $rolesArr['roles'][0],
        ]);
    }
}