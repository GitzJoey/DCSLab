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

    public function test_auth_api_register()
    {
        $this->markTestSkipped('Test under construction');

        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'User registered successfully',
        ]);
    }

    public function test_auth_api_login()
    {
        $this->markTestSkipped('Test under construction');

        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'token',
        ]);
    }

    public function test_auth_api_logout()
    {
        $this->markTestSkipped('Test under construction');
        
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'User logged out successfully',
        ]);
    }
}
