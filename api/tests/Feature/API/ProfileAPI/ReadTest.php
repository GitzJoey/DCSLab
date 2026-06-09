<?php

namespace Tests\Feature\API\ProfileAPI;

use App\Enums\UserRole;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Enums\RecordStatus;
use Illuminate\Support\Facades\Hash;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class ReadTest extends APITestCase
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

    public function test_profile_api_call_read_profile_expect_result()
    {
        $user = $this->createUserWithRole();

        $this->actingAs($user);

        $api = $this->json('GET', route('api.dashboard.profile.profile'));

        $api->assertSuccessful();
        
        $api->assertJsonFragment([
            'id' => Hashids::encode($user->id),
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }
}