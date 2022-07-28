<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use WithFaker;

    public function test_new_users_can_register()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => $this->faker->email(),
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => 'on'
        ]);

        $this->assertAuthenticated();
        $response->assertNoContent();
    }
}
