<?php

namespace Tests\Feature\API;

use App\Models\User;
use Tests\APITestCase;
use App\Actions\RandomGenerator;
use App\Services\CompanyService;
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

    public function test_api_call_read_when_user_have_companies()
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
        $pageSize = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $pageSize,
            
        ]));

        $api->assertSuccessful();
    }

    public function test_api_call_read_when_user_doesnt_have_companies()
    {
        $user = User::doesnthave('companies')->get();

        if ($user->count() == 0) {
            $email = $this->faker->email;
            $selectedUser = $this->userService->register('testing', $email, 'password', 'on');
        } else {
            $selectedUser = $user->shuffle()->first();
        }

        $this->actingAs($this->user);

        $userId = $selectedUser->id;
        $search = '';
        $paginate = (new RandomGenerator())->generateNumber(0, 1);
        $pageSize = 10;

        $api = $this->getJson(route('api.get.db.company.company.read', [
            'userId' => $userId,
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $pageSize,
            
        ]));

        $api->assertSuccessful();
    }

    public function test_api_call_save()
    {
        $this->actingAs($this->user);

        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $default = 1;
        $status = 1;
        $userId = $this->user->id;

        $api = $this->json('POST', route('api.post.db.company.company.save'), [
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'default' => 0,            
            'status' => $status,
            'userId' => $userId
        ]);

        $api->assertSuccessful();
    }

    public function test_api_call_edit()
    {
        $this->actingAs($this->user);

        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $default = 1;
        $status = 1;
        $userId = $this->user->id;

        $companyService = app(CompanyService::class);
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

    public function test_api_call_delete()
    {
        $this->actingAs($this->user);

        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $default = 0;
        $status = 1;
        $userId = $this->user->id;

        $companyService = app(CompanyService::class);
        $response = $companyService->create(
            $code,
            $name,
            $address,
            $default,
            $status,
            $userId
        );
        $companyId = $response->id;

        $api = $this->json('POST', route('api.post.db.company.company.delete', $companyId));

        $api->assertSuccessful();
    }
}
