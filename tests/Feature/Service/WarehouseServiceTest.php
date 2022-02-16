<?php

namespace Tests\Feature\Service;

use TypeError;
use App\Models\Warehouse;
use App\Models\Company;
use Tests\ServiceTestCase;
use App\Services\WarehouseService;
use App\Actions\RandomGenerator;
use Illuminate\Support\Collection;
use Vinkla\Hashids\Facades\Hashids;
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
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $response = $this->service->create(
            $company_id,
            $code,
            $name,
            $address,
            $city,
            $contact,
            $remarks,
            $status
        );

        $this->assertNotNull($response);

        $this->assertDatabaseHas('warehousees', [
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name,
            'address' => $address,
            'city' => $city,
            'contact' => $contact,
            'remarks' => $remarks,
            'status' => $status,
        ]);
    }

    public function test_call_update_warehousees_table()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = null;
        $status = 1;

        $response = $this->service->create(
            $company_id,
            $code,
            $name,
            $address,
            $city,
            $contact,
            $remarks,
            $status
        );

        $this->assertNotNull($response);

        $response_edit = $this->service->update(
            id: $response->id,
            company_id: '1',
            code: 'newCode',
            name: 'newName',
            address: 'newAddress',
            city: 'newCity',
            contact: 'newContact',
            remarks: 'newRemarks',
            status: '0'
        );

        $this->assertNotNull($response_edit);
        
        $this->assertDatabaseHas('warehousees', [
            'id' => $response_edit->id,
            'company_id' => '1',
            'code' => 'newCode',
            'name' => 'newName',
            'address' => 'newAddress',
            'city' => 'newCity',
            'contact' => 'newContact',
            'remarks' => 'newRemarks',
            'status' => 0,
        ]);
    }

    public function test_call_delete()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $address = $this->faker->address;
        $city = $this->faker->city;
        $contact = $this->faker->e164PhoneNumber;
        $remarks = null;
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $response = $this->service->create(
            $company_id,
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
        $deleted_at = Warehouse::withTrashed()->find($id)->deleted_at->format('Y-m-d H:i:s');
        
        $this->assertSoftDeleted('warehousees', [
            'id' => $id,
            'deleted_at' => $deleted_at
        ]);
    }
}
