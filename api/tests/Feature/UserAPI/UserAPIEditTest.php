<?php

namespace Tests\Feature\UserAPI;

use App\Enums\UserRoles;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class UserAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_user_api_call_update_expect_successful()
    {
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                ->create();

        $this->actingAs($user);

        $user = User::factory()
            ->setCreatedAt()->setUpdatedAt()
            ->has(Profile::factory()->setCreatedAt()->setUpdatedAt())
            ->hasAttached(Role::where('name', '=', UserRoles::ADMINISTRATOR->value)->first())
            ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
            ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
            ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
            ->create();

        $userArr = User::factory()->make()->toArray();
        $userArr = array_merge($userArr, Profile::factory()->make()->toArray());

        $userArr['roles'][0] = HashIds::encode(Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);
        $userArr['theme'] = Setting::factory()->createDefaultSetting_PREF_THEME()->make();
        $userArr['date_format'] = Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT()->make();
        $userArr['time_format'] = Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT()->make();

        $api = $this->json('POST', route('api.post.db.admin.users.edit', $user->ulid), $userArr);

        $api->assertSuccessful();

        $this->assertDatabaseHas('users', [
            'name' => $userArr['name'],
        ]);

        $this->assertDatabaseHas('profiles', [
            'address' => $userArr['address'],
            'city' => $userArr['city'],
            'postal_code' => $userArr['postal_code'],
            'country' => $userArr['country'],
            'tax_id' => $userArr['tax_id'],
            'ic_num' => $userArr['ic_num'],
            'status' => $userArr['status'],
            'remarks' => $userArr['remarks'],
        ]);
    }
}
