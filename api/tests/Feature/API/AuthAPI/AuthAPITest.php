<?php

namespace Tests\Feature\API\AuthAPI;

use App\Models\Profile;
use App\Models\User;
use Carbon\Carbon;
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
        ];

        $api = $this->json('POST', '/register', $userArr);

        $api->assertCreated();

        $this->assertDatabaseHas('users', [
            'name' => $userArr['name'],
            'email' => $userArr['email'],
        ]);
    }

    public function test_auth_api_call_register_with_missing_name_expect_validation_error()
    {
        $userArr = [
            'email' => User::factory()->make()->only('email')['email'],
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $api = $this->json('POST', '/register', $userArr);

        $api->assertUnprocessable();
    }

    public function test_auth_api_call_register_with_missing_email_expect_validation_error()
    {
        $userArr = [
            'name' => User::factory()->make()->only('name')['name'],
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $api = $this->json('POST', '/register', $userArr);

        $api->assertUnprocessable();
    }

    public function test_auth_api_call_register_with_invalid_email_expect_validation_error()
    {
        $userArr = [
            'name' => User::factory()->make()->only('name')['name'],
            'email' => 'not-a-valid-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $api = $this->json('POST', '/register', $userArr);

        $api->assertUnprocessable();
    }

    public function test_auth_api_call_register_with_duplicate_email_expect_validation_error()
    {
        $existingUser = User::factory()->create();

        $userArr = [
            'name' => User::factory()->make()->only('name')['name'],
            'email' => $existingUser->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $api = $this->json('POST', '/register', $userArr);

        $api->assertUnprocessable();
    }

    public function test_auth_api_call_register_with_password_mismatch_expect_validation_error()
    {
        $userArr = [
            'name' => User::factory()->make()->only('name')['name'],
            'email' => User::factory()->make()->only('email')['email'],
            'password' => 'password',
            'password_confirmation' => 'different-password',
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

    public function test_auth_api_call_login_with_wrong_password_expect_unauthorized()
    {
        $user = User::factory()->create();

        $userArr = [
            'email' => $user->email,
            'password' => 'wrong-password',
        ];

        $api = $this->json('POST', '/login', $userArr);

        $api->assertUnprocessable();
    }

    public function test_auth_api_call_login_with_nonexistent_email_expect_unauthorized()
    {
        $userArr = [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ];

        $api = $this->json('POST', '/login', $userArr);

        $api->assertUnprocessable();
    }

    public function test_auth_api_call_login_with_missing_email_expect_validation_error()
    {
        $userArr = [
            'password' => 'password',
        ];

        $api = $this->json('POST', '/login', $userArr);

        $api->assertUnprocessable();
    }

    public function test_auth_api_call_login_with_missing_password_expect_validation_error()
    {
        $user = User::factory()->create();

        $userArr = [
            'email' => $user->email,
        ];

        $api = $this->json('POST', '/login', $userArr);

        $api->assertUnprocessable();
    }

    public function test_auth_api_call_logout_expect_successful()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $api = $this->json('POST', '/logout');

        $api->assertNoContent();
    }

    public function test_auth_api_call_logout_without_authentication_expect_redirect()
    {
        $api = $this->json('POST', '/logout');

        $api->assertUnauthorized();
    }

    public function test_api_auth_controller_auth_expect_successful()
    {
        $user = User::factory()
            ->setNotRequiredResetPassword()
            ->has(Profile::factory()->setStatusActive())
            ->create();

        $api = $this->json('POST', '/api/auth', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $api->assertOk();
    }

    public function test_api_auth_controller_auth_expect_token_in_response()
    {
        $user = User::factory()
            ->setNotRequiredResetPassword()
            ->has(Profile::factory()->setStatusActive())
            ->create();

        $api = $this->json('POST', '/api/auth', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $api->assertOk();
        $api->assertJsonPath('tokens.token_type', 'Bearer');
        $api->assertJsonStructure([
            'tokens' => ['access_token', 'token_type'],
        ]);
    }

    public function test_api_auth_controller_auth_with_wrong_password_expect_error()
    {
        $user = User::factory()
            ->setNotRequiredResetPassword()
            ->has(Profile::factory()->setStatusActive())
            ->create();

        $api = $this->json('POST', '/api/auth', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $api->assertStatus(500);
    }

    public function test_api_auth_controller_auth_with_nonexistent_email_expect_error()
    {
        $api = $this->json('POST', '/api/auth', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $api->assertStatus(500);
    }

    public function test_api_auth_controller_auth_with_missing_email_expect_validation_error()
    {
        $api = $this->json('POST', '/api/auth', [
            'password' => 'password',
        ]);

        $api->assertUnprocessable();
    }

    public function test_api_auth_controller_auth_with_inactive_user_expect_validation_error()
    {
        $user = User::factory()
            ->setNotRequiredResetPassword()
            ->has(Profile::factory()->setStatusInactive())
            ->create();

        $api = $this->json('POST', '/api/auth', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $api->assertUnprocessable();
    }

    public function test_api_auth_controller_auth_with_max_tokens_exceeded_expect_validation_error()
    {
        $user = User::factory()
            ->setNotRequiredResetPassword()
            ->has(Profile::factory()->setStatusActive())
            ->create();

        $user->createToken('token-1');
        $user->createToken('token-2');
        $user->createToken('token-3');

        $api = $this->json('POST', '/api/auth', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $api->assertUnprocessable();
    }

    public function test_api_auth_controller_auth_with_null_password_changed_at_expect_validation_error()
    {
        $user = User::factory()
            ->has(Profile::factory()->setStatusActive())
            ->create(['password_changed_at' => null]);

        $api = $this->json('POST', '/api/auth', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $api->assertUnprocessable();
    }

    public function test_api_auth_controller_auth_with_expired_password_expect_validation_error()
    {
        $user = User::factory()
            ->has(Profile::factory()->setStatusActive())
            ->create([
                'password_changed_at' => Carbon::now()->subDays(91),
            ]);

        $api = $this->json('POST', '/api/auth', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $api->assertUnprocessable();
    }

    public function test_api_auth_controller_auth_when_already_authenticated_expect_redirect()
    {
        $user = User::factory()
            ->setNotRequiredResetPassword()
            ->has(Profile::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $api = $this->json('POST', '/api/auth', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $api->assertRedirect();
    }
}
