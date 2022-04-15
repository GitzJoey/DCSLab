<?php

namespace Tests\Feature\API;

use Tests\APITestCase;
use App\Models\Company;
use App\Models\Warehouse;
use App\Actions\RandomGenerator;
use App\Services\WarehouseService;
use Illuminate\Container\Container;
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

    public function test_api_call_save_with_all_field_filled()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
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
        $this->assertDatabaseHas('warehouses', [
            'code' => $code,
            'name' => $name
        ]);
    }

    public function test_api_call_save_with_minimal_field_filled()
    {
        $this->actingAs($this->user);

        $companyId = $this->user->companies->random(1)->first()->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = '';
        $city = '';
        $contact = '';
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
        $this->assertDatabaseHas('warehouses', [
            'code' => $code,
            'name' => $name
        ]);
    }

    public function test_api_call_save_with_existing_code()
    {
        $this->actingAs($this->user);

        $companyId = $this->user->companies->random(1)->first()->id;
        $code = $this->user->companies->random(1)->first()->code;
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

        $api->assertStatus(500);       
        $api->assertJsonStructure([
            'errors'
        ]);
    }

    public function test_api_call_save_with_null_param()
    {
        $this->actingAs($this->user);

        $companyId = null;
        $code = null;
        $name = null;
        $address = null;
        $city = null;
        $contact = null;
        $remarks = null;
        $status = null;

        $api = $this->json('POST', route('api.post.db.company.warehouse.save'), [
            'company_id' => $companyId,
            'code' => $code, 
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status
        ]);

        $api->assertStatus(500);
        $api->assertJsonStructure([
            'message'
        ]);
    }

    public function test_api_call_save_with_empty_string_param()
    {
        $this->actingAs($this->user);

        $companyId = '';
        $code = '';
        $name = '';
        $address = '';
        $city = '';
        $contact = '';
        $remarks = '';
        $status = '';

        $api = $this->json('POST', route('api.post.db.company.warehouse.save'), [
            'company_id' => $companyId,
            'code' => $code, 
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status
        ]);

        $api->assertStatus(500);
        $api->assertJsonStructure([
            'message'
        ]);
    }

    public function test_api_call_edit_with_all_field_filled()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = $this->faker->sentence;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $warehouse = Warehouse::create([
            'company_id' => $companyId,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status
        ]);
        $warehouseId = $warehouse->id;

        $newCode = (new RandomGenerator())->generateAlphaNumeric(5) . 'new';
        $newName = $this->faker->name;
        $newAddress = $this->faker->address;
        $newCity = $this->faker->city;
        $newContact = $this->faker->e164PhoneNumber;
        $newRemarks = $this->faker->sentence;
        $newStatus = (new RandomGenerator())->generateNumber(0, 1);

        $api_edit = $this->json('POST', route('api.post.db.company.warehouse.edit', [ 'id' => Hashids::encode($warehouseId) ]), [
            'company_id' => Hashids::encode($companyId),
            'code' => $newCode,
            'name' => $newName,
            'address' => $newAddress,
            'city' => $newCity,
            'contact' => $newContact,
            'remarks' => $newRemarks,
            'status' => $newStatus,
        ]);

        $api_edit->assertSuccessful();
        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouseId,
            'code' => $newCode
        ]);
    }

    public function test_api_call_edit_with_minimal_field_filled()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = null;
        $city = null;
        $contact = null;
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $warehouse = Warehouse::create([
            'company_id' => $companyId,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status
        ]);
        $warehouseId = $warehouse->id;

        $newCode = (new RandomGenerator())->generateAlphaNumeric(5) . 'new';
        $newName = $this->faker->name;
        $newAddress = null;
        $newCity = null;
        $newContact = null;
        $newRemarks = null;
        $newStatus = (new RandomGenerator())->generateNumber(0, 1);

        $api_edit = $this->json('POST', route('api.post.db.company.warehouse.edit', [ 'id' => Hashids::encode($warehouseId) ]), [
            'company_id' => Hashids::encode($companyId),
            'code' => $newCode,
            'name' => $newName,
            'address' => $newAddress,
            'city' => $newCity,
            'contact' => $newContact,
            'remarks' => $newRemarks,
            'status' => $newStatus,
        ]);

        $api_edit->assertSuccessful();
        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouseId,
            'code' => $newCode
        ]);
    }

    public function test_api_call_edit_with_existing_code()
    {
        $this->actingAs($this->user);

        $companyId = $this->user->companies->random(1)->first()->id;

        for ($i = 0; $i < 3; $i++) {
            $code = $this->user->companies->random(1)->first()->code;
            $name = $this->faker->name;
            $address = $this->faker->address;
            $city = $this->faker->city;
            $contact = $this->faker->e164PhoneNumber;
            $remarks = $this->faker->sentence();
            $status = (new RandomGenerator())->generateNumber(0, 1);
    
            Warehouse::create([
                'company_id' => $companyId,
                'code' => $code,
                'name' => $name,
                'address' => $address,
                'city' => $city,
                'contact' => $contact,
                'remarks' => $remarks,
                'status' => $status
            ]);
        }

        $warehouseId = Warehouse::where('company_id', $companyId)->inRandomOrder()->first()->id;
        $newCode = Warehouse::where('company_id', $companyId)->whereNotIn('id', [$warehouseId])->inRandomOrder()->first()->id;
        $newName = $this->faker->name;
        $newAddress = $this->faker->address;
        $newCity = $this->faker->city;
        $newContact = $this->faker->e164PhoneNumber;
        $newRemarks = $this->faker->sentence();
        $newStatus = (new RandomGenerator())->generateNumber(0, 1);

        $api_edit = $this->json('POST', route('api.post.db.company.warehouse.edit', [ 'id' => Hashids::encode($warehouseId) ]), [
            'company_id' => Hashids::encode($companyId),
            'code' => $newCode,
            'name' => $newName,
            'address' => $newAddress,
            'city' => $newCity,
            'contact' => $newContact,
            'remarks' => $newRemarks,
            'status' => $newStatus,
        ]);

        $api_edit->assertStatus(500);
        $api_edit->assertJsonStructure([
            'message'
        ]);
    }

    public function test_api_call_edit_with_null_param()
    {
        $this->actingAs($this->user);

        $api_edit = $this->json('POST', route('api.post.db.company.warehouse.edit', Hashids::encode((new RandomGenerator())->generateNumber(1, 9999))), [
            'company_id' => null,
            'code' => null,
            'name' => null,
            'address' => null,
            'city' => null,
            'contact' => null,
            'remarks' => null,
            'status' => null
        ]);

        $api_edit->assertStatus(500);
        $api_edit->assertJsonStructure([
            'message'
        ]);
    }

    public function test_api_call_delete()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = $this->faker->sentence;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $warehouse = Warehouse::create([
            'company_id' => $companyId,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status
        ]);
        $warehouseId = $warehouse->id;

        $this->json('POST', route('api.post.db.company.warehouse.delete', Hashids::encode($warehouseId)));

        $this->assertSoftDeleted('warehouses', [
            'id' => $warehouseId
        ]);
    }

    public function test_api_call_delete_nonexistance_id()
    {
        $this->actingAs($this->user);

        $api = $this->json('POST', route('api.post.db.company.warehouse.delete', (new RandomGenerator())->generateAlphaNumeric(5)));
 
        $api->assertStatus(500);
        $api->assertJsonStructure([
            'message'
        ]);
    }

    public function test_api_call_read_with_empty_search()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;
        $search = "";
        $paginate = (new RandomGenerator())->generateNumber(0, 1);
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.warehouse.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $perPage
        ]));

        $api->assertSuccessful();
    }

    public function test_api_call_read_with_special_char_in_search()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;
        $search = " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";
        $paginate = (new RandomGenerator())->generateNumber(0, 1);
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.warehouse.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $perPage
        ]));

        $api->assertSuccessful();
    }

    public function test_api_call_read_with_negative_value_in_perpage_param()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;
        $search = '';
        $paginate = 1;
        $perPage = -10;
        
        $api = $this->getJson(route('api.get.db.company.warehouse.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'paginate' => $paginate,
            'perPage' => $perPage
        ]));

        $api->assertStatus(500);
        $api->assertJsonStructure([
            'message'
        ]);
    }

    public function test_api_call_read_without_pagination()
    {
        $this->actingAs($this->user);

        $companyId = Company::inRandomOrder()->get()[0]->id;
        $search = '';
        $perPage = 10;
        
        $api = $this->getJson(route('api.get.db.company.warehouse.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'perPage' => $perPage
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data', 
            'links' => [
                'first', 'last', 'prev', 'next'
            ], 
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'
            ]
        ]);
    }

    public function test_api_call_read_with_null_param()
    {
        $this->actingAs($this->user);
        
        $api = $this->getJson(route('api.get.db.company.warehouse.read', [
            'companyId' => null,
            'search' => null,
            'paginate' => null,
            'perPage' => null
        ]));

        $api->assertStatus(500);
        $api->assertJsonStructure([
            'message'
        ]);
    }
}
