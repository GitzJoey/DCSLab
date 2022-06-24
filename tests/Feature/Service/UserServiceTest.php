<?php

namespace Tests\Feature\Service;

use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Tests\ServiceTestCase;
use TypeError;

class UserServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(UserService::class);

        if (User::count() < 2)
            $this->artisan('db:seed', ['--class' => 'UserTableSeeder']);
    }

    public function test_user_service_call_read_with_empty_search()
    {
        $response = $this->service->read('', true, 10);

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertNotNull($response);
    }

    public function test_user_service_call_read_with_special_char_in_search()
    {
        $response = $this->service->read('&', true, 10);

        $this->assertNotNull($response);
        $this->assertInstanceOf(Paginator::class, $response);
    }

    public function test_user_service_call_read_with_negative_value_in_perpage_param()
    {
        $response = $this->service->read('', true, -10);

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertNotNull($response);
    }

    public function test_user_service_call_read_without_pagination()
    {
        $response = $this->service->read('', false, 10);

        $this->assertInstanceOf(Collection::class, $response);
    }

    public function test_user_service_call_read_with_null_param()
    {
        $this->expectException(TypeError::class);

        $this->service->read(null, null, null);
    }

    public function test_user_service_call_register()
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

    public function test_user_service_call_register_with_existing_email()
    {
        $email = $this->faker->email;
        $usr = $this->service->register('normaluser', $email, 'password', 'on');

        $usr = $this->service->register('normaluser', $usr->email, 'password', 'on');

        $this->assertNull($usr);
    }

    public function test_user_service_call_create()
    {
        $email = $this->faker->email;
        $roles = Role::get()->pluck('id')->toArray();
        $profile = [ 
            'first_name' => 'first_name',
            'status' => 1 
        ];

        $response = $this->service->create('testname', $email, 'password', $roles, $profile);

        $this->assertNotNull($response);

        $this->assertDatabaseHas('users', [
            'name' => 'testname',
            'email' => $email
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'testname',
            'email' => $email
        ]);

        $this->assertDatabaseHas('profiles', [
            'first_name' => 'first_name',
            'status' => 1
        ]);

        $this->assertDatabaseHas('settings', [
            'user_id' => $response->id,
        ]);

        $this->assertDatabaseHas('role_user', [
            'user_id' => $response->id,
            'role_id' => $roles[0]
        ]);
    }

    public function test_user_service_call_create_with_empty_profile_array()
    {
        $email = $this->faker->email;
        $roles = Role::get()->pluck('id')->toArray();
        $profile = [];

        $response = $this->service->create('testname', $email, 'password', $roles, $profile);

        $this->assertNotNull($response);

        $this->assertDatabaseHas('users', [
            'name' => 'testname',
            'email' => $email
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'testname',
            'email' => $email
        ]);

        $this->assertDatabaseHas('profiles', [
            'first_name' => '',
            'status' => 1
        ]);

        $this->assertDatabaseHas('settings', [
            'user_id' => $response->id,
        ]);

        $this->assertDatabaseHas('role_user', [
            'user_id' => $response->id,
            'role_id' => $roles[0]
        ]);
    }

    public function test_user_service_call_create_with_empty_roles_array()
    {
        $email = $this->faker->email;
        $roles = [];
        $profile = [];

        $response = $this->service->create('testname', $email, 'password', $roles, $profile);

        $this->assertNotNull($response);
        
        $this->assertDatabaseMissing('role_user', [
            'user_id' => $response->id
        ]);
    }

    public function test_user_service_call_update_users_table()
    {
        $email = $this->faker->email;
        $roles = Role::get()->pluck('id')->toArray();;
        $profile = [];

        $response = $this->service->create('testname', $email, 'password', $roles, $profile);

        $this->assertNotNull($response);

        $response_edit = $this->service->update(id: $response->id, name: 'editedname');

        $this->assertNotNull($response_edit);
        
        $this->assertDatabaseHas('users', [
            'id' => $response_edit->id,
            'name' => 'editedname'
        ]);
    }

    public function test_user_service_call_update_profile_table()
    {
        $email = $this->faker->email;
        $roles = Role::get()->pluck('id')->toArray();
        $profile = [];

        $response = $this->service->create('testname', $email, 'password', $roles, $profile);

        $this->assertNotNull($response);

        $profile_new = [
            'first_name' => 'edited first name'
        ];

        $response_edit = $this->service->update(id: $response->id, name: $response->name, profile: $profile_new);

        $this->assertNotNull($response_edit);
        
        $this->assertDatabaseHas('profiles', [
            'user_id' => $response_edit->id,
            'first_name' => 'edited first name'
        ]);        
    }

    public function test_user_service_call_update_profile_table_update_one_field_others_must_remain()
    {
        $this->markTestSkipped('Under Construction');
        
        $email = $this->faker->email;
        $roles = Role::get()->pluck('id')->toArray();
        $profile = [
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'address' => 'address',
            'city' => 'city',
            'postal_code' => 'postal_code',
            'country' => 'country',
            'status' => 'status',
            'tax_id' => 'tax_id',
            'ic_num' => 'ic_num',
            'img_path' => 'img_path',
            'remarks' => 'remarks'
        ];

        $response = $this->service->create('testname', $email, 'password', $roles, $profile);

        $this->assertNotNull($response);

        $profile_new = [
            'first_name' => 'edited first name'
        ];

        $response_edit = $this->service->update(id: $response->id, name: $response->name, profile: $profile_new);

        $this->assertNotNull($response_edit);
        
        $this->assertDatabaseHas('profiles', [
            'user_id' => $response_edit->id,
            'first_name' => 'edited first name',
            'last_name' => 'last_name',
            'address' => 'address',
            'city' => 'city',
            'postal_code' => 'postal_code',
            'country' => 'country',
            'status' => 'status',
            'tax_id' => 'tax_id',
            'ic_num' => 'ic_num',
            'img_path' => 'img_path',
            'remarks' => 'remarks'
        ]);

        $profile_new = [
            'last_name' => 'edited last name'
        ];

        $response_edit = $this->service->update(id: $response->id, name: $response->name, profile: $profile_new);

        $this->assertNotNull($response_edit);
        $this->assertDatabaseHas('profiles', [
            'user_id' => $response_edit->id,
            'first_name' => 'edited first name',
            'last_name' => 'edited last name',
            'address' => 'address',
            'city' => 'city',
            'postal_code' => 'postal_code',
            'country' => 'country',
            'status' => 'status',
            'tax_id' => 'tax_id',
            'ic_num' => 'ic_num',
            'img_path' => 'img_path',
            'remarks' => 'remarks'
        ]);

        $profile_new = [
            'address' => 'edited address'
        ];

        $response_edit = $this->service->update(id: $response->id, name: $response->name, profile: $profile_new);

        $this->assertNotNull($response_edit);
        
        $this->assertDatabaseHas('profiles', [
            'user_id' => $response_edit->id,
            'first_name' => 'edited first name',
            'last_name' => 'edited last name',
            'address' => 'edited address',
            'city' => 'city',
            'postal_code' => 'postal_code',
            'country' => 'country',
            'status' => 'status',
            'tax_id' => 'tax_id',
            'ic_num' => 'ic_num',
            'img_path' => 'img_path',
            'remarks' => 'remarks'
        ]);
    }
}
