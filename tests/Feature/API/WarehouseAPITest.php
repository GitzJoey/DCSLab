<?php

namespace Tests\Feature\API;

use App\Actions\RandomGenerator;
use App\Models\Warehouse;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

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

        $companyId = Company::inRandomOrder()->get()[0]->id;;
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

        $companyId = Company::inRandomOrder()->get()[0]->id;
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

        $warehouse = Warehouse::with('company')->inRandomOrder()->first();

        $code_new = (new RandomGenerator())->generateNumber(1, 9999) . 'new';
        $name_new = $this->faker->name;
        $address_new = $this->faker->address;
        $city_new = $this->faker->city;
        $contact_new = $this->faker->e164PhoneNumber;
        $remarks_new = '';
        $status_new = (new RandomGenerator())->generateNumber(0, 1);

        $api_edit = $this->json('POST', route('api.post.db.company.warehouse.edit', [ 'id' => $warehouse->hId ]), [
            'company_id' => $warehouse->company->hId,
            'code' => $code_new, 
            'name' => $name_new,
            'address' => $address_new,
            'city' => $city_new,
            'contact' => $contact_new,
            'remarks' => $remarks_new,
            'status' => $status_new
        ]);

        $api_edit->assertSuccessful();
    }

    public function test_api_call_delete()
    {
        $this->actingAs($this->user);

        $warehouse = Warehouse::with('company')->inRandomOrder()->first();

        $api = $this->json('POST', route('api.post.db.company.warehouse.delete', [ 'id' => $warehouse->hId ]));

        $api->assertSuccessful();
    }
}
