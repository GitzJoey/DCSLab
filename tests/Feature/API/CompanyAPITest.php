<?php

namespace Tests\Feature\API;

use App\Models\User;
use Tests\APITestCase;
use App\Models\Company;
use App\Services\UserService;
use App\Actions\RandomGenerator;
use App\Services\CompanyService;
use Illuminate\Container\Container;
use Vinkla\Hashids\Facades\Hashids;
use Database\Seeders\CompanyTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompanyAPITest extends APITestCase
{
    use WithFaker;

    public function test_api_call_require_authentication()
    {
        $api = $this->getJson('/api/get/dashboard/company/company/read');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/company/save');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/company/edit/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/company/delete/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));
    }

    public function test_api_call_read_when_user_have_companies_read_with_empty_search()
    {
        if (User::count() == 0)
            $this->artisan('db:seed', ['--class' => 'UserTableSeeder']);

        if (User::has('companies')->count() == 0) {
            $companyPerUser = 3;
            $companySeeder = new CompanyTableSeeder();
            $companySeeder->callWith(CompanyTableSeeder::class, [$companyPerUser]);    
        }

        $this->actingAs($this->user);

        $userId = User::has('companies')->get()->first()->id;
        $search = "";
        $paginate = (new RandomGenerator())->generateNumber(0, 1);
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $perPage,
            
        ]));

        $api->assertSuccessful();
    }

    public function test_api_call_read_when_user_have_companies_with_special_char_in_search()
    {
        if (User::count() == 0)
            $this->artisan('db:seed', ['--class' => 'UserTableSeeder']);

        if (User::has('companies')->count() == 0) {
            $companyPerUser = 3;
            $companySeeder = new CompanyTableSeeder();
            $companySeeder->callWith(CompanyTableSeeder::class, [$companyPerUser]);    
        }

        $this->actingAs($this->user);

        $userId = User::has('companies')->get()->first()->id;
        $search = " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";
        $paginate = (new RandomGenerator())->generateNumber(0, 1);
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $perPage,
            
        ]));

        $api->assertSuccessful();
    }

    public function test_api_call_read_when_user_have_companies_with_negative_value_in_perpage_param()
    {
        if (User::count() == 0)
            $this->artisan('db:seed', ['--class' => 'UserTableSeeder']);

        if (User::has('companies')->count() == 0) {
            $companyPerUser = 3;
            $companySeeder = new CompanyTableSeeder();
            $companySeeder->callWith(CompanyTableSeeder::class, [$companyPerUser]);    
        }

        $this->actingAs($this->user);

        $userId = User::has('companies')->get()->first()->id;
        $search = '';
        $paginate = (new RandomGenerator())->generateNumber(0, 1);
        $perPage = -10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $perPage,
            
        ]));

        $api->assertStatus(500);
    }

    public function test_api_call_read_when_user_have_companies_without_pagination()
    {
        if (User::count() == 0)
            $this->artisan('db:seed', ['--class' => 'UserTableSeeder']);

        if (User::has('companies')->count() == 0) {
            $companyPerUser = 3;
            $companySeeder = new CompanyTableSeeder();
            $companySeeder->callWith(CompanyTableSeeder::class, [$companyPerUser]);    
        }

        $this->actingAs($this->user);

        $userId = User::has('companies')->get()->first()->id;
        $search = '';
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'perPage' => $perPage,
            
        ]));

        $api->assertSuccessful();
    }

    public function test_api_call_read_when_user_have_companies_with_null_param()
    {
        if (User::count() == 0)
            $this->artisan('db:seed', ['--class' => 'UserTableSeeder']);

        if (User::has('companies')->count() == 0) {
            $companyPerUser = 3;
            $companySeeder = new CompanyTableSeeder();
            $companySeeder->callWith(CompanyTableSeeder::class, [$companyPerUser]);    
        }

        $this->actingAs($this->user);

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => null,
            'search' => null,
            'paginate' => null,
            'perPage' => null,
            
        ]));

        $api->assertSuccessful();
    }

    public function test_api_call_read_when_user_doesnt_have_companies_with_empty_search()
    {
        $container = Container::getInstance();
        $userService = $container->make(UserService::class);

        $user = User::doesnthave('companies')->get();

        if ($user->count() == 0) {
            $email = $this->faker->email;
            $selectedUser = $userService->register('testing', $email, 'password', 'on');
        } else {
            $selectedUser = $user->shuffle()->first();
        }

        $this->actingAs($this->user);

        $userId = $selectedUser->id;
        $search = '';
        $paginate = (new RandomGenerator())->generateNumber(0, 1);
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $perPage,
            
        ]));

        $api->assertSuccessful();
    }

    public function test_api_call_read_when_user_doesnt_have_companies_with_special_char_in_search()
    {
        $container = Container::getInstance();
        $userService = $container->make(UserService::class);

        $user = User::doesnthave('companies')->get();

        if ($user->count() == 0) {
            $email = $this->faker->email;
            $selectedUser = $userService->register('testing', $email, 'password', 'on');
        } else {
            $selectedUser = $user->shuffle()->first();
        }

        $this->actingAs($this->user);

        $userId = $selectedUser->id;
        $search = " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";
        $paginate = (new RandomGenerator())->generateNumber(0, 1);
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $perPage,
            
        ]));

        $api->assertSuccessful();
    }

    public function test_api_call_read_when_user_doesnt_have_companies_with_negative_value_in_perpage_param()
    {
        $container = Container::getInstance();
        $userService = $container->make(UserService::class);

        $user = User::doesnthave('companies')->get();

        if ($user->count() == 0) {
            $email = $this->faker->email;
            $selectedUser = $userService->register('testing', $email, 'password', 'on');
        } else {
            $selectedUser = $user->shuffle()->first();
        }

        $this->actingAs($this->user);

        $userId = $selectedUser->id;
        $search = '';
        $paginate = (new RandomGenerator())->generateNumber(0, 1);
        $perPage = -10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $perPage,
            
        ]));

        $api->assertStatus(500);
    }

    public function test_api_call_read_when_user_doesnt_have_companies_without_pagination()
    {
        $container = Container::getInstance();
        $userService = $container->make(UserService::class);

        $user = User::doesnthave('companies')->get();

        if ($user->count() == 0) {
            $email = $this->faker->email;
            $selectedUser = $userService->register('testing', $email, 'password', 'on');
        } else {
            $selectedUser = $user->shuffle()->first();
        }

        $this->actingAs($this->user);

        $userId = $selectedUser->id;
        $search = '';
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'perPage' => $perPage,
            
        ]));

        $api->assertSuccessful();
    }

    public function test_api_call_read_when_user_doesnt_have_companies_with_null_param()
    {
        $container = Container::getInstance();
        $userService = $container->make(UserService::class);

        $user = User::doesnthave('companies')->get();

        if ($user->count() == 0) {
            $email = $this->faker->email;
            $selectedUser = $userService->register('testing', $email, 'password', 'on');
        } else {
            $selectedUser = $user->shuffle()->first();
        }

        $this->actingAs($this->user);

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => null,
            'search' => null,
            'paginate' => null,
            'perPage' => null,
            
        ]));

        $api->assertSuccessful();
    }

    public function test_api_call_save_with_all_field_filled()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;;
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = $this->faker->sentence();
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $api = $this->json('POST', route('api.post.db.company.warehouse.save'), [
            'company_id' => Hashids::encode($companyId),
            'code' => $code, 
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status
        ]);

        $api->assertSuccessful();
    }

    public function test_api_call_save_with_minimal_field_filled()
    {
        $this->actingAs($this->user);

        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $address = '';
        $default = '';
        $status = 1;
        $userId = $this->user->id;

        $api = $this->json('POST', route('api.post.db.company.company.save'), [
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'default' => $default,
            'status' => $status,
            'userId' => $userId
        ]);

        $api->assertSuccessful();
    }

    public function test_api_call_save_with_existing_code()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->company_id;
        $code = Company::where('company_id', $companyId)->inRandomOrder()->first()->code;
        $name = $this->faker->name;
        $address = $this->faker->address;
        $default = 1;
        $status = 1;
        $userId = $this->user->id;

        $api = $this->json('POST', route('api.post.db.company.company.save'), [
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'default' => $default,
            'status' => $status,
            'userId' => $userId
        ]);

        $api->assertStatus(422);
    }

    public function test_api_call_save_with_null_param()
    {
        $this->actingAs($this->user);

        $code = null;
        $name = null;
        $address = null;
        $default = null;
        $status = null;
        $userId = null;

        $api = $this->json('POST', route('api.post.db.company.company.save'), [
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'default' => $default,
            'status' => $status,
            'userId' => $userId
        ]);

        $api->assertStatus(500);
    }

    public function test_api_call_edit_with_all_field_filled()
    {
        $this->actingAs($this->user);

        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $default = 1;
        $status = 1;
        $userId = $this->user->id;

        $container = Container::getInstance();
        $companyService = $container->make(CompanyService::class);

        $companyId = $companyService->create(
            $code,
            $name,
            $address,
            $default,
            $status,
            $userId
        );
        $companyId = $companyId->id;

        $api_edit = $this->json('POST', route('api.post.db.company.company.edit', [
            'id' => $companyId,
            'code' => (new RandomGenerator())->generateNumber(1, 9999) . 'new',
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'default' => 1,
            'status' => 1
        ]));

        $api_edit->assertSuccessful();
    }

    public function test_api_call_edit_with_minimal_field_filled()
    {
        $this->actingAs($this->user);

        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $address = null;
        $default = 0;
        $status = 1;
        $userId = $this->user->id;

        $container = Container::getInstance();
        $companyService = $container->make(CompanyService::class);

        $companyId = $companyService->create(
            $code,
            $name,
            $address,
            $default,
            $status,
            $userId
        );
        $companyId = $companyId->id;

        $api_edit = $this->json('POST', route('api.post.db.company.company.edit', [
            'id' => $companyId,
            'code' => (new RandomGenerator())->generateNumber(1, 9999) . 'new',
            'name' => $this->faker->name,
            'address' => null,
            'default' => 0,
            'status' => 1
        ]));

        $api_edit->assertSuccessful();
    }

    public function test_api_call_edit_with_existing_code()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->company_id;
        $code = Company::where('company_id', $companyId)->inRandomOrder()->first()->code;
        $name = $this->faker->name;
        $address = $this->faker->address;
        $default = 1;
        $status = 1;
        $userId = $this->user->id;

        $container = Container::getInstance();
        $companyService = $container->make(CompanyService::class);

        $companyId = $companyService->create(
            $code,
            $name,
            $address,
            $default,
            $status,
            $userId
        );
        $companyId = $companyId->id;

        $api_edit = $this->json('POST', route('api.post.db.company.company.edit', [
            'id' => $companyId,
            'code' => (new RandomGenerator())->generateNumber(1, 9999) . 'new',
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'default' => 1,
            'status' => 1
        ]));
    }

    public function test_api_call_edit_with_null_param()
    {
        $this->actingAs($this->user);

        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $default = 1;
        $status = 1;
        $userId = $this->user->id;

        $container = Container::getInstance();
        $companyService = $container->make(CompanyService::class);

        $companyId = $companyService->create(
            $code,
            $name,
            $address,
            $default,
            $status,
            $userId
        );
        $companyId = $companyId->id;

        $api_edit = $this->json('POST', route('api.post.db.company.company.edit', [
            'id' => $companyId,
            'code' => null,
            'name' => null,
            'address' => null,
            'default' => 0,
            'status' => null
        ]));

        $api_edit->assertStatus(500);
    }

    public function test_api_call_delete()
    {
        $this->actingAs($this->user);

        $isDefault = 0;
        do {
            $code = (new RandomGenerator())->generateNumber(1,9999);
            $name = $this->faker->name;
            $address = $this->faker->address;
            $default = 0;
            $status = 1;
            $userId = $this->user->id;
    
            $container = Container::getInstance();
            $companyService = $container->make(CompanyService::class);
            
            $response = $companyService->create(
                $code,
                $name,
                $address,
                $default,
                $status,
                $userId
            );
            $companyId = $response->id;

            $isDefault = Company::where('id', $companyId)->first()->default;
        } while ($isDefault == 1);

        $api = $this->json('POST', route('api.post.db.company.company.delete', $companyId));

        $api->assertSuccessful();
    }
}