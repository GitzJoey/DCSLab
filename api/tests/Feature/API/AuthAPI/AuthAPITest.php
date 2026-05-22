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

    public function test_auth_api_call_register_expect_new_record_in_database()
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

        $this->assertDatabaseHas('users', [
            'name' => $userArr['name'],
            'email' => $userArr['email'],
        ]);
    }

    public function test_auth_api_call_register_only_allow_name_with_alpha_numeric()
    {
        $userArr = [
            'name' => 'test!?&',
            'email' => User::factory()->make()->only('email')['email'],
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => true,
        ];

        $api = $this->json('POST', '/register', $userArr);
        $api->assertUnprocessable();

        $userArr['name'] = 'with space';
        $api = $this->json('POST', '/register', $userArr);
        $api->assertUnprocessable();

        $userArr['name'] = 'with[bracket]';
        $api = $this->json('POST', '/register', $userArr);
        $api->assertUnprocessable();

        $userArr['name'] = 'with[bracket]';
        $api = $this->json('POST', '/register', $userArr);
        $api->assertUnprocessable();
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
            'password' => 'password',
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

    public function test_auth_api_call_rejected_because_password_is_expired()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_auth_api_call_rejected_because_user_id_is_inactive()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_auth_api_call_api_auth_expect_token_created()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_auth_api_call_api_auth_where_token_already_created_twice_expect_unsuccesful()
    {
        $this->markTestSkipped('Under Constructions');
    }
}
