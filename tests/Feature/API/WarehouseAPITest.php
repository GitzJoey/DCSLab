<?php

namespace Tests\Feature\API;

use Tests\APITestCase;
use App\Models\Company;
use App\Models\Warehouse;
use App\Enums\ActiveStatus;
use App\Actions\RandomGenerator;
use App\Services\WarehouseService;
use Illuminate\Container\Container;
use Vinkla\Hashids\Facades\Hashids;
use Database\Seeders\BranchTableSeeder;
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
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;

        $branchSeeder = new BranchTableSeeder();
        $branchSeeder->callWith(BranchTableSeeder::class, [3, $companyId]);
        $branchId = $this->developer->companies->where('id', '=', $companyId)->first()->branches->random(1)->first()->id;

        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = $this->faker->sentence();
        $status = $this->faker->randomElement(ActiveStatus::toArrayName());

        $api = $this->json('POST', route('api.post.db.company.warehouse.save'), [
            'company_id' => Hashids::encode($companyId),
            'branch_id' => Hashids::encode($branchId),
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
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => ActiveStatus::fromName($status)
        ]);
    }

    public function test_api_call_save_with_minimal_field_filled()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;

        $branchSeeder = new BranchTableSeeder();
        $branchSeeder->callWith(BranchTableSeeder::class, [3, $companyId]);
        $branchId = $this->developer->companies->where('id', '=', $companyId)->first()->branches->random(1)->first()->id;

        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = null;
        $city = null;
        $contact = null;
        $remarks = null;
        $status = $this->faker->randomElement(ActiveStatus::toArrayName());

        $api = $this->json('POST', route('api.post.db.company.warehouse.save'), [
            'company_id' => Hashids::encode($companyId),
            'branch_id' => Hashids::encode($branchId),
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
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => ActiveStatus::fromName($status)
        ]);
    }

    public function test_api_call_save_with_existing_code()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;

        $branchSeeder = new BranchTableSeeder();
        $branchSeeder->callWith(BranchTableSeeder::class, [3, $companyId]);
        $branchId = $this->developer->companies->where('id', '=', $companyId)->first()->branches->random(1)->first()->id;

        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = $this->faker->sentence();
        $status = (new RandomGenerator())->generateNumber(0, 1);

        Warehouse::create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status
        ]);

        $code = Warehouse::whereIn('company_id', [$companyId])->inRandomOrder()->first()->code;
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = $this->faker->sentence();
        $status = $this->faker->randomElement(ActiveStatus::toArrayName());

        $api = $this->json('POST', route('api.post.db.company.warehouse.save'), [
            'company_id' => Hashids::encode($companyId),
            'branch_id' => Hashids::encode($branchId),
            'code' => $code, 
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status
        ]);

        $api->assertStatus(422);       
        $api->assertJsonStructure([
            'errors'
        ]);
    }

    public function test_api_call_save_with_empty_string_param()
    {
        $this->actingAs($this->developer);

        $companyId = '';
        $branchId = '';
        $code = '';
        $name = '';
        $address = '';
        $city = '';
        $contact = '';
        $remarks = '';
        $status = '';

        $api = $this->json('POST', route('api.post.db.company.warehouse.save'), [
            'company_id' => $companyId,
            'branch_id' => $branchId,
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
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;

        $branchSeeder = new BranchTableSeeder();
        $branchSeeder->callWith(BranchTableSeeder::class, [3, $companyId]);
        $branchId = $this->developer->companies->where('id', '=', $companyId)->first()->branches->random(1)->first()->id;

        $warehouse = Warehouse::create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'code' => (new RandomGenerator())->generateAlphaNumeric(5),
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'contact' => $this->faker->e164PhoneNumber,
            'remarks' => $this->faker->sentence,
            'status' => $this->faker->randomElement(ActiveStatus::toArrayValue())
        ]);
        $warehouseId = $warehouse->id;
        
        $newName = $this->faker->name;
        $newCode = (new RandomGenerator())->generateAlphaNumeric(5).'new';
        $newAddress = $this->faker->address;
        $newCity = $this->faker->city;
        $newContact = $this->faker->e164PhoneNumber;
        $newRemarks = $this->faker->sentence;
        $newStatus = $this->faker->randomElement(ActiveStatus::toArrayName());

        $api_edit = $this->json('POST', route('api.post.db.company.warehouse.edit', [ 'id' => Hashids::encode($warehouseId) ]), [
            'company_id' => Hashids::encode($companyId),
            'branch_id' => Hashids::encode($branchId),
            'code' => $newCode,
            'name' => $newName,
            'address' => $newAddress,
            'city' => $newCity,
            'contact' => $newContact,
            'remarks' => $newRemarks,
            'status' => $newStatus
        ]);

        $api_edit->assertSuccessful();
        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouseId,
            'code' => $newCode,
            'name' => $newName,
            'address' => $newAddress,
            'city' => $newCity,
            'contact' => $newContact,
            'remarks' => $newRemarks,
            'status' => ActiveStatus::fromName($newStatus)
        ]);
    }

    public function test_api_call_edit_with_minimal_field_filled()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;

        $branchSeeder = new BranchTableSeeder();
        $branchSeeder->callWith(BranchTableSeeder::class, [3, $companyId]);
        $branchId = $this->developer->companies->where('id', '=', $companyId)->first()->branches->random(1)->first()->id;

        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = null;
        $city = null;
        $contact = null;
        $remarks = null;
        $status = $this->faker->randomElement(ActiveStatus::toArrayValue());

        $warehouse = Warehouse::create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
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
        $newStatus = $this->faker->randomElement(ActiveStatus::toArrayName());

        $api_edit = $this->json('POST', route('api.post.db.company.warehouse.edit', [ 'id' => Hashids::encode($warehouseId) ]), [
            'company_id' => Hashids::encode($companyId),
            'branch_id' => Hashids::encode($branchId),
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
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;

        $branchSeeder = new BranchTableSeeder();
        $branchSeeder->callWith(BranchTableSeeder::class, [3, $companyId]);
        $branchId = $this->developer->companies->where('id', '=', $companyId)->first()->branches->random(1)->first()->id;

        for ($i = 0; $i < 3; $i++) {
            $code = (new RandomGenerator())->generateAlphaNumeric(5);
            $name = $this->faker->name;
            $address = $this->faker->address;
            $city = $this->faker->city;
            $contact = $this->faker->e164PhoneNumber;
            $remarks = $this->faker->sentence();
            $status = $this->faker->randomElement(ActiveStatus::toArrayValue());
    
            Warehouse::create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
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
        $newStatus = $this->faker->randomElement(ActiveStatus::toArrayName());

        $api_edit = $this->json('POST', route('api.post.db.company.warehouse.edit', [ 'id' => Hashids::encode($warehouseId) ]), [
            'company_id' => Hashids::encode($companyId),
            'branch_id' => Hashids::encode($branchId),
            'code' => $newCode,
            'name' => $newName,
            'address' => $newAddress,
            'city' => $newCity,
            'contact' => $newContact,
            'remarks' => $newRemarks,
            'status' => ActiveStatus::fromName($newStatus)
        ]);

        $api_edit->assertStatus(500);
        $api_edit->assertJsonStructure([
            'message'
        ]);
    }

    public function test_api_call_delete()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;

        $branchSeeder = new BranchTableSeeder();
        $branchSeeder->callWith(BranchTableSeeder::class, [3, $companyId]);
        $branchId = $this->developer->companies->where('id', '=', $companyId)->first()->branches->random(1)->first()->id;

        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = $this->faker->sentence;
        $status = $this->faker->randomElement(ActiveStatus::toArrayValue());

        $warehouse = Warehouse::create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
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
        $this->actingAs($this->developer);

        $api = $this->json('POST', route('api.post.db.company.warehouse.delete', (new RandomGenerator())->generateAlphaNumeric(5)));
 
        $api->assertStatus(500);
        $api->assertJsonStructure([
            'message'
        ]);
    }

    public function test_api_call_read_with_empty_search()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;

        $branchSeeder = new BranchTableSeeder();
        $branchSeeder->callWith(BranchTableSeeder::class, [3, $companyId]);
        $branchId = $this->developer->companies->where('id', '=', $companyId)->first()->branches->random(1)->first()->id;

        $search = "";
        $paginate = 1;
        $page = 1;
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.warehouse.read', [
            'companyId' => Hashids::encode($companyId),
            'branchId' => Hashids::encode($branchId),
            'search' => $search,
            'paginate' => $paginate,
            'page' => $page,
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

    public function test_api_call_read_with_special_char_in_search()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;
        $search = " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";
        $paginate = 1;
        $page = 1;
        $perPage = 10;

        $api = $this->getJson(route('api.get.db.company.warehouse.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'paginate' => $paginate,
            'page' => $page,
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

    public function test_api_call_read_with_negative_value_in_perpage_param()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;
        $search = '';
        $paginate = 1;
        $page = 1;
        $perPage = -10;
        
        $api = $this->getJson(route('api.get.db.company.warehouse.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'paginate' => $paginate,
            'page' => $page,
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

    public function test_api_call_read_without_pagination()
    {
        $this->actingAs($this->developer);

        $companyId = $this->developer->companies->random(1)->first()->id;
        $search = '';
        $page = 1;
        $perPage = 10;
        
        $api = $this->getJson(route('api.get.db.company.warehouse.read', [
            'companyId' => Hashids::encode($companyId),
            'search' => $search,
            'page' => $page,
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
}
