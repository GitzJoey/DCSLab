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

    public function test_profile_api_call_read_profile_expect_result()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_profile_api_call_update_user_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->create();

        $this->actingAs($user);

        $userArr = User::factory()->make()->toArray();

        $api = $this->json('POST', route('api.post.db.module.profile.update.user_profile'), $userArr);

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

        $api = $this->json('POST', route('api.post.db.module.profile.update.personal_info'), $profileArr);

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
            'current_password' => 'password',
            'password' => $password,
            'password_confirmation' => $password,
        ];

        $api = $this->json('POST', route('api.post.db.module.profile.update.password'), $changePasswordArr);

        $api->assertSuccessful();

        $this->assertTrue(Hash::check($password, $user->password));
    }

    public function test_profile_api_call_update_settings_expect_successful()
    {
        $this->markTestSkipped('Test under construction');

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
            ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
            ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
            ->create();

        $this->actingAs($user);

        $settingsArr = [
            'theme' => [
                'key' => 'PREFS.THEME',
                'value' => 'test_theme',
            ],
            'date_format' => [
                'key' => 'PREFS.DATE_FORMAT',
                'value' => 'yyyy-MMM-dd',
            ],
            'time_format' => [
                'key' => 'PREFS.TIME_FORMAT',
                'value' => 'hh:mm:ss',
            ],
        ];

        $api = $this->json('POST', route('api.post.db.module.profile.update.setting'), $settingsArr);

        $api->assertSuccessful();

        foreach ($settingsArr as $setting) {
            $this->assertDatabaseHas('settings', [
                'user_id' => $user->id,
                'key' => $setting['key'],
                'value' => $setting['value'],
            ]);
        }
    }

    public function test_profile_api_call_update_roles_expect_successful()
    {
        $this->markTestSkipped('Test under construction');

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Role::factory()->count(3))
            ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
            ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
            ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
            ->create();

        $this->actingAs($user);

        $rolesArr['roles'] = [
            Role::inRandomOrder()->first()->id,
        ];

        $api = $this->json('POST', route('api.post.db.module.profile.update.roles'), $rolesArr);

        $api->assertSuccessful();

        $this->assertDatabaseHas('role_user', [
            'user_id' => $user->id,
            'role_id' => $rolesArr['roles'][0],
        ]);
    }
}
