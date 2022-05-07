<?php

namespace Tests\Feature\Service;

use TypeError;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Warehouse;
use Tests\ServiceTestCase;
use App\Actions\RandomGenerator;
use App\Services\WarehouseService;
use Illuminate\Support\Collection;
use Vinkla\Hashids\Facades\Hashids;
use Database\Seeders\BranchTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WarehouseServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(WarehouseService::class);

        if (Warehouse::count() < 2)
            $this->artisan('db:seed', ['--class' => 'WarehouseTableSeeder']);
    }

    public function test_call_read_with_empty_search()
    {
        $response = $this->service->read(1,'', true, 10);

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertNotNull($response);
    }

    public function test_call_read_with_special_char_in_search()
    {
        $response = $this->service->read(1,'&', true, 10);

        $this->assertNotNull($response);
        $this->assertInstanceOf(Paginator::class, $response);
    }

    public function test_call_read_with_negative_value_in_perpage_param()
    {
        $response = $this->service->read(-1,'', true, -10);

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertNotNull($response);
    }

    public function test_call_read_without_pagination()
    {
        $response = $this->service->read(1,'', false, 10);

        $this->assertInstanceOf(Collection::class, $response);
    }

    public function test_call_read_with_null_param()
    {
        $this->expectException(TypeError::class);

        $this->service->read(null, null, null);
    }

    public function test_call_create()
    {
        $companyId = Company::inRandomOrder()->get()[0]->id;

        $branchSeeder = new BranchTableSeeder();
        $branchSeeder->callWith(BranchTableSeeder::class, [3, $companyId]);
        $branchId = Branch::where('company_id', '=', $companyId)->inRandomOrder()->first()->id;
        
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $response = $this->service->create(
            $companyId,
            $branchId,
            $code,
            $name,
            $address,
            $city,
            $contact,
            $remarks,
            $status
        );

        $this->assertNotNull($response);

        $this->assertDatabaseHas('warehouses', [
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status,
        ]);
    }

    public function test_call_update()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $branch_id = Branch::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = null;
        $status = 1;

        $response = $this->service->create(
            $company_id,
            $branch_id,
            $code,
            $name,
            $address,
            $city,
            $contact,
            $remarks,
            $status
        );

        $this->assertNotNull($response);

        $code_new = (new RandomGenerator())->generateNumber(1, 9999);
        $name_new = $this->faker->name;
        $address_new = $this->faker->address;
        $city_new = $this->faker->city;
        $contact_new = $this->faker->e164PhoneNumber;
        $remarks_new = null;
        $status_new = (new RandomGenerator())->generateNumber(0, 1);

        $response_edit = $this->service->update(
            id: $response->id,
            company_id: $company_id,
            branch_id: $branch_id,
            code: $code_new,
            name: $name_new,
            address: $address_new,
            city: $city_new,
            contact: $contact_new,
            remarks: $remarks_new,
            status: $status_new
        );

        $this->assertNotNull($response_edit);
        
        $this->assertDatabaseHas('warehouses', [
            'id' => $response_edit->id,
            'company_id' => $company_id,
            'branch_id' => $branch_id,
            'code' => $code_new,
            'name' => $name_new,
            'address' => $address_new,
            'city' => $city_new,
            'contact' => $contact_new,
            'remarks' => $remarks_new,
            'status' => $status_new
        ]);
    }

    public function test_call_delete()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $branch_id = Branch::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $response = $this->service->create(
            $company_id,
            $branch_id,
            $code,
            $name,
            $address,
            $city,
            $contact,
            $remarks,
            $status
        );

        $id = $response->id;

        $this->service->delete($id);
        
        $this->assertSoftDeleted('warehouses', [
            'id' => $id
        ]);
    }
    
    public function test_call_delete_with_nonexistence_id()
    {
        $max_int = 2147483647;
        
        $response = $this->service->delete($max_int);

        $this->assertFalse($response);
    }
}
