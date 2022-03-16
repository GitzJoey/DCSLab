<?php

namespace Tests\Feature\API;

use Tests\APITestCase;
use App\Models\Company;
use App\Models\Warehouse;
use App\Actions\RandomGenerator;
use App\Services\WarehouseService;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WarehouseAPITest extends APITestCase
{
    use WithFaker;

    public function test_api_call_require_authentication()
    {
        $api = $this->getJson('/api/get/dashboard/company/warehouse/read');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/warehouse/save');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/warehouse/edit/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));

        $api = $this->getJson('/api/post/dashboard/company/warehouse/delete/1');
        $this->assertContains($api->getStatusCode(), array(401, 405));
    }

    public function test_api_call_read()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;
        $page = 1;
        $pageSize = 10;
        $search = '';

        $api = $this->getJson(route('api.get.db.company.warehouse.read', [
            'companyId' => Hashids::encode($companyId),
            'page' => $page,
            'perPage' => $pageSize,
            'search' => $search
        ]));

        $api->assertSuccessful();
    }

    public function test_api_call_save()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;;
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = '';
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

    public function test_api_call_edit()
    {
        $this->actingAs($this->user);
        $companyId = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);
        $warehouseService = app(WarehouseService::class);
        $warehouseId = $warehouseService->create(
            $companyId,
            $code,
            $name,
            $address,
            $city,
            $contact,
            $remarks,
            $status
        );
        $warehouseId = $warehouseId->id;

        $api_edit = $this->json('POST', route('api.post.db.company.warehouse.edit', [ 'id' => $warehouseId ]), [
            'company_id' => Hashids::encode(Company::inRandomOrder()->get()[0]->id),
            'code' => (new RandomGenerator())->generateNumber(1, 9999) . 'new',
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'contact' => $this->faker->e164PhoneNumber,
            'remarks' => $this->faker->sentence,
            'status' => (new RandomGenerator())->generateNumber(0, 1),
        ]);

        $api_edit->assertSuccessful();
    }

    public function test_api_call_delete()
    {
        $this->actingAs($this->user);

        $this->actingAs($this->user);
        $companyId = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);
        $warehouseService = app(WarehouseService::class);
        $warehouseId = $warehouseService->create(
            $companyId,
            $code,
            $name,
            $address,
            $city,
            $contact,
            $remarks,
            $status
        );
        $warehouseId = $warehouseId->id;

        $api = $this->json('POST', route('api.post.db.company.warehouse.delete', $warehouseId));

        $api->assertSuccessful();
    }
}
