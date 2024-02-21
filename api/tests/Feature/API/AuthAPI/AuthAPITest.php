<?php

namespace Tests\Feature\API\AuthAPI;

use App\Models\User;
use Tests\APITestCase;

class AuthAPITest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_auth_api_call_register_expect_successful()
    {
        $userArr = [
            'name' => User::factory()->make()->only('name')['name'],
            'email' => User::factory()->make()->only('email')['email'],
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => true,
        ];

        $api = $this->json('POST', '/register', $userArr);

        $api->assertCreated();
    }

    public function test_auth_api_call_register_without_terms_expect_unsuccessful()
    {
        $userArr = [
            'name' => User::factory()->make()->only('name')['name'],
            'email' => User::factory()->make()->only('email')['email'],
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $api = $this->json('POST', '/register', $userArr);

        $api->assertUnprocessable();
    }

    public function test_auth_api_call_login_expect_successful()
    {
        $user = User::factory()->create();

        $userArr = [
            'email' => $user->email,
            'password' => 'password'
        ];

        $api = $this->json('POST', '/login', $userArr);

        $api->assertOk();
    }

    public function test_auth_api_call_logout_expect_successful()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $api = $this->json('POST', '/logout');

        $api->assertNoContent();
    }
}
