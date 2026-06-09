<?php

namespace Tests\Feature\API\DashboardAPI;

use App\Enums\RecordStatus;
use App\Models\Profile;
use App\Models\User;
use Tests\APITestCase;

class ReadTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function createValidDashboardUser(): User
    {
        $user = User::factory()->create([
            'password_changed_at' => now(),
        ]);

        Profile::factory()->create([
            'user_id' => $user->id,
            'status' => RecordStatus::ACTIVE,
        ]);

        return $user;
    }

    public function test_authenticated_active_user_can_access_menu()
    {
        $user = $this->createValidDashboardUser();
        $url = route('api.dashboard.menu');

        $response = $this->actingAs($user)->json('GET', $url);

        $response->assertOk();
    }

    public function test_authenticated_active_user_can_access_routes()
    {
        $user = $this->createValidDashboardUser();
        $url = route('api.dashboard.routes');

        $response = $this->actingAs($user)->json('GET', $url);

        $response->assertOk();
    }

    public function test_user_cannot_access_dashboard_if_password_changed_at_is_null()
    {
        $user = User::factory()->create(['password_changed_at' => null]);
        Profile::factory()->create(['user_id' => $user->id, 'status' => RecordStatus::ACTIVE]);

        $url = route('api.dashboard.menu');
        $response = $this->actingAs($user)->json('GET', $url);

        $response->assertStatus(403);
        $response->assertJsonPath('message', __('middleware.validate_user.must_reset_password'));
    }

    public function test_user_cannot_access_dashboard_if_password_is_expired()
    {
        $user = User::factory()->create([
            'password_changed_at' => now()->subDays(95),
        ]);
        Profile::factory()->create(['user_id' => $user->id, 'status' => RecordStatus::ACTIVE]);

        $url = route('api.dashboard.menu');
        $response = $this->actingAs($user)->json('GET', $url);

        $response->assertStatus(403);
        $response->assertJsonPath('message', __('middleware.validate_user.must_reset_password'));
    }

    public function test_user_cannot_access_dashboard_if_profile_is_inactive()
    {
        $user = User::factory()->create(['password_changed_at' => now()]);

        Profile::factory()->create([
            'user_id' => $user->id,
            'status' => RecordStatus::INACTIVE,
        ]);

        $url = route('api.dashboard.menu');
        $response = $this->actingAs($user)->json('GET', $url);

        $response->assertStatus(403);
        $response->assertJsonPath('message', __('middleware.validate_user.inactive_user'));
    }

    public function test_user_cannot_access_dashboard_if_profile_is_missing()
    {
        $user = User::factory()->create(['password_changed_at' => now()]);

        $url = route('api.dashboard.menu');
        $response = $this->actingAs($user)->json('GET', $url);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_guest_is_blocked_from_dashboard()
    {
        $url = route('api.dashboard.menu');

        $response = $this->json('GET', $url);

        $response->assertStatus(401);
    }
}
