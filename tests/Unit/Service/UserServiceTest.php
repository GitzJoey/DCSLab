<?php

namespace Tests\Unit\Service;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\TestCase;
use TypeError;

class UserServiceTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(UserService::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_call_read_with_empty_search()
    {
        $response = $this->service->read('', true, 10);

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertTrue(!is_null($response));
    }

    public function test_call_read_with_special_char_in_search()
    {
        $response = $this->service->read('', true, 10);

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertTrue(!is_null($response));
    }

    public function test_call_read_with_negative_value_in_perpage_param()
    {
        $response = $this->service->read('', true, -10);

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertTrue(!is_null($response));
    }

    public function test_call_read_without_pagination()
    {
        $response = $this->service->read('', false, 10);

        $this->assertInstanceOf(Collection::class, $response);
    }

    public function test_call_read_with_null_param()
    {
        $this->expectException(TypeError::class);

        $this->service->read(null, null, null);
    }

    public function test_call_register()
    {
        $email = $this->faker->email;
        $usr = $this->service->register('normaluser', $email, 'password', 'on');

        $this->assertDatabaseHas('users', [
            'name' => 'normaluser',
            'email' => $email
        ]);

        $this->assertDatabaseHas('role_user', [
            'user_id' => $usr->id
        ]);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $usr->id
        ]);

        $this->assertDatabaseHas('settings', [
            'user_id' => $usr->id
        ]);
    }

    public function test_call_register_with_existing_email()
    {
        $usr = User::inRandomOrder()->first();

        $usr = $this->service->register('normaluser', $usr->email, 'password', 'on');

        $this->assertTrue(is_null($usr));
    }

    public function test_call_create()
    {
        $this->assertTrue(true);
    }

    public function test_call_update()
    {
        $this->assertTrue(true);
    }

    public function test_call_delete()
    {
        $this->assertTrue(true);
    }
}
