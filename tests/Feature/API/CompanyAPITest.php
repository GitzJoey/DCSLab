<?php

namespace Tests\Feature\API;

use Tests\APITestCase;
use App\Actions\RandomGenerator;
use App\Services\CompanyService;
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

    public function test_api_call_read()
    {
        $this->actingAs($this->user);

        $userId = $this->user->id;
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
        $companyId = $companyService->create(
            $code,
            $name,
            $address,
            $default,
            $status,
            $userId
        );
        $companyId = $companyId->id;

        $api = $this->json('POST', route('api.post.db.company.company.delete', $companyId));

        $api->assertSuccessful();
    }
}
