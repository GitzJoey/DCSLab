<?php

namespace Tests\Feature\Service;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Warehouse;
use Tests\ServiceTestCase;
use App\Actions\RandomGenerator;
use App\Services\WarehouseService;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;

class WarehouseServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(WarehouseService::class);

        if (Company::count() < 2) {
            $this->artisan('db:seed', ['--class' => 'CompanyTableSeeder']);
        }

        if (Branch::count() < 2) {
            $this->artisan('db:seed', ['--class' => 'BranchTableSeeder']);
        }

        if (Warehouse::count() < 2) {
            $this->artisan('db:seed', ['--class' => 'WarehouseTableSeeder']);
        }
    }

    public function test_call_save_with_all_field_filled()
    {
        $branch_id = Branch::inRandomOrder()->first()->id;
        $company_id = Branch::where('id', '=', $branch_id)->first()->company_id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = $this->faker->sentence;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $this->service->create(
            company_id: $company_id,
            branch_id: $branch_id,
            code: $code,
            name: $name,
            address: $address,
            city: $city,
            contact: $contact,
            remarks: $remarks,
            status: $status
        );

        $this->assertDatabaseHas('warehouses', [
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'code' => $code,
            'name' => $name
        ]);
    }

    public function test_call_save_with_minimal_field_filled()
    {
        $branch_id = Branch::inRandomOrder()->first()->id;
        $company_id = Branch::where('id', '=', $branch_id)->first()->company_id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = null;
        $city = null;
        $contact = null;
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $this->service->create(
            company_id: $company_id,
            branch_id: $branch_id,
            code: $code,
            name: $name,
            address: $address,
            city: $city,
            contact: $contact,
            remarks: $remarks,
            status: $status
        );

        $this->assertDatabaseHas('warehouses', [
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'code' => $code,
            'name' => $name
        ]);
    }

    public function test_call_edit_with_all_field_filled()
    {
        $branch_id = Branch::inRandomOrder()->first()->id;
        $company_id = Branch::where('id', '=', $branch_id)->first()->company_id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = $this->faker->sentence;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $warehouse = Warehouse::create([
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status
        ]);
        $id = $warehouse->id;

        $newCode = (new RandomGenerator())->generateAlphaNumeric(5);
        $newName = $this->faker->name;
        $newAddress = $this->faker->address;
        $newCity = $this->faker->city;
        $newContact = $this->faker->e164PhoneNumber;
        $newRemarks = $this->faker->sentence;
        $newStatus = (new RandomGenerator())->generateNumber(0, 1);

        $this->service->update(
            id: $id,
            company_id: $company_id,
            branch_id: $branch_id,
            code: $newCode,
            name: $newName,
            address: $newAddress,
            city: $newCity,
            contact: $newContact,
            remarks: $newRemarks,
            status: $newStatus
        );

        $this->assertDatabaseHas('warehouses', [
            'id' => $id,
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'code' => $newCode,
            'name' => $newName
        ]);
    }

    public function test_call_edit_with_minimal_field_filled()
    {
        $branch_id = Branch::inRandomOrder()->first()->id;
        $company_id = Branch::where('id', '=', $branch_id)->first()->company_id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = null;
        $city = null;
        $contact = null;
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $warehouse = Warehouse::create([
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status
        ]);
        $id = $warehouse->id;

        $newCode = (new RandomGenerator())->generateAlphaNumeric(5);
        $newName = $this->faker->name;
        $newAddress = null;
        $newCity = null;
        $newContact = null;
        $newRemarks = null;
        $newStatus = (new RandomGenerator())->generateNumber(0, 1);

        $this->service->update(
            id: $id,
            company_id: $company_id,
            branch_id: $branch_id,
            code: $newCode,
            name: $newName,
            address: $newAddress,
            city: $newCity,
            contact: $newContact,
            remarks: $newRemarks,
            status: $newStatus
        );

        $this->assertDatabaseHas('warehouses', [
            'id' => $id,
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'code' => $newCode,
            'name' => $newName
        ]);
    }

    public function test_call_delete()
    {
        $branch_id = Branch::inRandomOrder()->first()->id;
        $company_id = Branch::where('id', '=', $branch_id)->first()->company_id;
        $code = (new RandomGenerator())->generateAlphaNumeric(5);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = $this->faker->sentence;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $warehouse = Warehouse::create([
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status
        ]);
        $id = $warehouse->id;

        $this->service->delete($id);

        $this->assertSoftDeleted('warehouses', [
            'id' => $id
        ]);
    }

    public function test_call_read_when_user_have_warehouses_read_with_empty_search()
    {
        $warehouseId = Warehouse::inRandomOrder()->first()->id;
        $companyId = Warehouse::where('id', '=', $warehouseId)->first()->company_id;

        $response = $this->service->read(
            companyId: $companyId, 
            search: '', 
            paginate: true, 
            page: 1,
            perPage: 10,
            useCache: false
        );

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertNotNull($response);
    }

    public function test_call_read_when_user_have_warehouses_with_special_char_in_search()
    {
        $warehouseId = Warehouse::inRandomOrder()->first()->id;
        $companyId = Warehouse::where('id', '=', $warehouseId)->first()->company_id;

        $search = " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~";
        $paginate = true;
        $page = 1;
        $perPage = 10;
        $useCache = false;

        $response = $this->service->read(
            companyId: $companyId, 
            search: $search, 
            paginate: $paginate, 
            page: $page,
            perPage: $perPage,
            useCache: $useCache
        );

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertNotNull($response);
    }
}
