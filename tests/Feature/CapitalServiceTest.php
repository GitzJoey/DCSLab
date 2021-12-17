<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Cash;
use App\Models\Capital;
use App\Models\Company;
use App\Models\Investor;
use App\Models\CapitalGroup;
use App\Services\CashService;
use App\Actions\RandomGenerator;
use App\Services\CapitalService;
use App\Services\InvestorService;
use Vinkla\Hashids\Facades\Hashids;
use App\Services\CapitalGroupService;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\WithFaker;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CapitalServiceTest extends TestCase
{
    use WithFaker;
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CapitalService::class);
        
        $this->investorService = app(InvestorService::class);
        $this->groupService = app(CapitalGroupService::class);
        $this->cashService = app(CashService::class);
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

        $faker = \Faker\Factory::create('id_ID');
        $this->investorService->create(
            $company_id,
            $faker->unique()->numberBetween(001, 99999),
            $faker->name(),
            $faker->e164PhoneNumber(),
            $faker->address(),
            $faker->city(),
            $faker->creditCardNumber(),
            null,
            (new RandomGenerator())->generateNumber(0, 1)
        );

        $this->groupService->create(
            $company_id,
            $faker->unique()->numberBetween(001, 99999),
            $faker->name()
        );

        $this->cashService->create(
            $company_id,
            $faker->unique()->numberBetween(001, 99999),
            $faker->name(),
            (new RandomGenerator())->generateNumber(0, 1),
            (new RandomGenerator())->generateNumber(0, 1)
        );

        $investor_id = Investor::inRandomOrder()->get()[0]->id;
        $group_id = CapitalGroup::inRandomOrder()->get()[0]->id;
        $cash_id = Cash::inRandomOrder()->get()[0]->id;
        $ref_number = (new RandomGenerator())->generateNumber(1, 9999);
        $date = Carbon::now()->toDateTimeString();
        $capital_status = (new RandomGenerator())->generateNumber(0, 1);
        $amount = $this->faker->randomDigit;
        $remarks = null;

        $this->service->create(
            $company_id,
            $ref_number,
            $investor_id,
            $group_id,
            $cash_id,
            $date,
            $capital_status,
            $amount,
            $remarks,
        );

        $this->assertDatabaseHas('capitals', [
            'company_id' => $company_id,
            'investor_id' => $investor_id,
            'group_id' => $group_id,
            'cash_id' => $cash_id,
            'ref_number' => $ref_number,
            'date' => $date,
            'capital_status' => $capital_status,
            'amount' => $amount,
            'remarks' => $remarks,
        ]);
    }

    public function test_update()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;

        $faker = \Faker\Factory::create('id_ID');
        $this->investorService->create(
            $company_id,
            $faker->unique()->numberBetween(001, 99999),
            $faker->name(),
            $faker->e164PhoneNumber(),
            $faker->address(),
            $faker->city(),
            $faker->creditCardNumber(),
            null,
            (new RandomGenerator())->generateNumber(0, 1)
        );

        $this->groupService->create(
            $company_id,
            $faker->unique()->numberBetween(001, 99999),
            $faker->name()
        );

        $this->cashService->create(
            $company_id,
            $faker->unique()->numberBetween(001, 99999),
            $faker->name(),
            (new RandomGenerator())->generateNumber(0, 1),
            (new RandomGenerator())->generateNumber(0, 1)
        );

        $investor_id = Investor::inRandomOrder()->get()[0]->id;
        $group_id = CapitalGroup::inRandomOrder()->get()[0]->id;
        $cash_id = Cash::inRandomOrder()->get()[0]->id;
        $ref_number = (new RandomGenerator())->generateNumber(1, 9999);
        $date = Carbon::now()->toDateTimeString();
        $capital_status = (new RandomGenerator())->generateNumber(0, 1);
        $amount = $this->faker->randomDigit;
        $remarks = null;

        $response = $this->service->create(
            $company_id,
            $ref_number,
            $investor_id,
            $group_id,
            $cash_id,
            $date,
            $capital_status,
            $amount,
            $remarks,
        );
        $rId = Hashids::decode($response)[0];

        $company_id_new = Company::inRandomOrder()->get()[0]->id;
        $investor_id_new = Investor::inRandomOrder()->get()[0]->id;
        $group_id_new = CapitalGroup::inRandomOrder()->get()[0]->id;
        $cash_id_new = Cash::inRandomOrder()->get()[0]->id;
        $ref_number_new = (new RandomGenerator())->generateNumber(1, 9999);
        $date_new = Carbon::now()->toDateTimeString();
        $capital_status_new = (new RandomGenerator())->generateNumber(0, 1);
        $amount_new = $this->faker->randomDigit;
        $remarks_new = null;
        $response = $this->service->update(
            $rId, 
            $company_id_new,
            $ref_number_new,
            $investor_id_new,
            $group_id_new,
            $cash_id_new,
            $date_new,
            $capital_status_new,
            $amount_new,
            $remarks_new,
        );

        $this->assertDatabaseHas('capitals', [
            'id' => $rId,
            'company_id' => $company_id_new,
            'investor_id' => $investor_id_new,
            'group_id' => $group_id_new,
            'cash_id' => $cash_id_new,
            'ref_number' => $ref_number_new,
            'date' => $date_new,
            'capital_status' => $capital_status_new,
            'amount' => $amount_new,
            'remarks' => $remarks_new,
        ]);
    }

    public function test_delete()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $investor_id = Investor::inRandomOrder()->get()[0]->id;
        $group_id = CapitalGroup::inRandomOrder()->get()[0]->id;
        $cash_id = Cash::inRandomOrder()->get()[0]->id;
        $ref_number = (new RandomGenerator())->generateNumber(1, 9999);
        $date = Carbon::now()->toDateTimeString();
        $capital_status = (new RandomGenerator())->generateNumber(0, 1);
        $amount = $this->faker->randomDigit;
        $remarks = null;
        $response = $this->service->create(
            $company_id,
            $ref_number,
            $investor_id,
            $group_id,
            $cash_id,
            $date,
            $capital_status,
            $amount,
            $remarks,
        );
        $rId = Hashids::decode($response)[0];

        $response = $this->service->delete($rId);
        $deleted_at = Capital::withTrashed()->find($rId)->deleted_at->format('Y-m-d H:i:s');
        
        $this->assertDatabaseHas('capitals', [
            'id' => $rId,
            'deleted_at' => $deleted_at
        ]);
    }
}
