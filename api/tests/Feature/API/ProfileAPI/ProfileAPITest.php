<?php

namespace Tests\Feature\API\ProfileAPI;

use App\Enums\UserRoles;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\APITestCase;

class ProfileAPITest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
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
