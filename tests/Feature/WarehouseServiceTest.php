<?php

namespace Tests\Feature;

use App\Services\WarehouseService;
use App\Models\Company;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Config;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Contracts\Pagination\Paginator;
use App\Actions\RandomGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WarehouseServiceTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(WarehouseService::class);
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_read()
    {
        $selectedCompanyId = Company::inRandomOrder()->get()[0]->id;
        session()->put(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'), Hashids::encode($selectedCompanyId));
        $response = $this->service->read();

        $this->assertInstanceOf(Paginator::class, $response);
        $this->assertTrue(!is_null($response));
        // $this->assertTrue(true);
    }
    
    public function test_create()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $address = $this->$faker->address();
        $city = $this->$warehouse_name;
        $contact = $this->$faker->e164PhoneNumber();
        $remarks = null;
        $status = '1';

        $this->service->create($company_id, $code, $name);
    
        $this->assertDatabaseHas('brands', [
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name
            
        ]);
    }

    public function test_update()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $response = $this->service->create($company_id, $code, $name);
        $rId = Hashids::decode($response)[0];

        $company_id_new = Company::inRandomOrder()->get()[0]->id;
        $code_new = (new RandomGenerator())->generateNumber(1,9999);
        $name_new = $this->faker->name;
        $response = $this->service->update($rId, $company_id_new, $code_new, $name_new);

        $this->assertDatabaseHas('brands', [
            'id' => $rId,
            'company_id' => $company_id_new,
            'code' => $code_new,
            'name' => $name_new
        ]);
    }

    public function test_delete()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $response = $this->service->create($company_id, $code, $name);
        $rId = Hashids::decode($response)[0];

        $response = $this->service->delete($rId);
        $deleted_at = Warehouse::withTrashed()->find($rId)->deleted_at->format('Y-m-d H:i:s');
        
        $this->assertDatabaseHas('brands', [
            'id' => $rId,
            'deleted_at' => $deleted_at
        ]);
    }
}
