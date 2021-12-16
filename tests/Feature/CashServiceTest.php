<?php

namespace Tests\Feature;

use App\Models\Cash;
use App\Services\CashService;
use App\Models\Company;
use Illuminate\Support\Facades\Config;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Contracts\Pagination\Paginator;
use App\Actions\RandomGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CashServiceTest extends TestCase
{
    use WithFaker;
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CashService::class);
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
    }
    
    public function test_create()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $is_bank = (new RandomGenerator())->generateNumber(0, 1);
        $status = (new RandomGenerator())->generateNumber(0, 1);

        $this->service->create(
            $company_id,
            $code,
            $name,
            $is_bank,
            $status
        );
    
        $this->assertDatabaseHas('cashes', [
            'company_id' => $company_id,
            'code' => $code,
            'name' => $name,
            'is_bank' => $is_bank,
            'status' => $status
        ]);
    }

    public function test_update()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $is_bank = (new RandomGenerator())->generateNumber(0, 1);
        $status = (new RandomGenerator())->generateNumber(0, 1);
        $response = $this->service->create(
            $company_id,
            $code,
            $name,
            $is_bank,
            $status
        );
        $rId = Hashids::decode($response)[0];

        $company_id_new = Company::inRandomOrder()->get()[0]->id;
        $code_new = (new RandomGenerator())->generateNumber(1,9999);
        $name_new = $this->faker->name;
        $is_bank_new = (new RandomGenerator())->generateNumber(0, 1);
        $status_new = (new RandomGenerator())->generateNumber(0, 1);
        $response = $this->service->update(
            $rId,
            $company_id_new,
            $code_new,
            $name_new,
            $is_bank_new,
            $status_new
        );

        $this->assertDatabaseHas('cashes', [
            'id' => $rId,
            'company_id' => $company_id_new,
            'code' => $code_new,
            'name' => $name_new,
            'is_bank' => $is_bank_new,
            'status' => $status_new
        ]);
    }

    public function test_delete()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1,9999);
        $name = $this->faker->name;
        $is_bank = (new RandomGenerator())->generateNumber(0, 1);
        $status = (new RandomGenerator())->generateNumber(0, 1);
        $response = $this->service->create(
            $company_id,
            $code,
            $name,
            $is_bank,
            $status
        );
        $rId = Hashids::decode($response)[0];

        $response = $this->service->delete($rId);
        $deleted_at = Cash::withTrashed()->find($rId)->deleted_at->format('Y-m-d H:i:s');
        
        $this->assertDatabaseHas('cashes', [
            'id' => $rId,
            'deleted_at' => $deleted_at
        ]);
    }
}
