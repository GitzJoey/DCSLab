<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Cash;
use App\Services\CustomerGroupService;
use App\Services\CashService;

use Illuminate\Support\Facades\Config;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Contracts\Pagination\Paginator;
use App\Actions\RandomGenerator;
use App\Models\CustomerGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerGroupServiceTest extends TestCase
{
    use WithFaker;
    
    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CustomerGroupService::class);
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
        $faker = \Faker\Factory::create('id_ID');

        $company_id = Company::inRandomOrder()->get()[0]->id;

        $this->cashService->create(
            $company_id,
            $faker->unique()->numberBetween(001, 99999),
            $faker->name(),
            (new RandomGenerator())->generateNumber(0, 1),
            (new RandomGenerator())->generateNumber(0, 1)
        );
        
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $max_open_invoice = (new RandomGenerator())->generateNumber(1, 9999);
        $max_outstanding_invoice = (new RandomGenerator())->generateNumber(1, 9999);
        $max_invoice_age = (new RandomGenerator())->generateNumber(1, 9999);
        $payment_term = (new RandomGenerator())->generateNumber(1, 365);
        $selling_point = (new RandomGenerator())->generateNumber(1, 9999);
        $selling_point_multiple = (new RandomGenerator())->generateNumber(1, 9999);
        $sell_at_cost = (new RandomGenerator())->generateNumber(1, 9999);
        $price_markup_percent = (new RandomGenerator())->generateNumber(1, 9999);
        $price_markup_nominal = (new RandomGenerator())->generateNumber(1, 9999);
        $price_markdown_percent = (new RandomGenerator())->generateNumber(1, 9999);
        $price_markdown_nominal = (new RandomGenerator())->generateNumber(1, 9999);
        $round_on = (new RandomGenerator())->generateNumber(0, 2);
        $round_digit = (new RandomGenerator())->generateNumber(1, 9999);
        $remarks = null;
        $cash_id = Cash::inRandomOrder()->get()[0]->id;
        $this->service->create(
            $company_id,
            $code,
            $name,
            $max_open_invoice,
            $max_outstanding_invoice,
            $max_invoice_age,
            $payment_term,
            $selling_point,
            $selling_point_multiple,
            $sell_at_cost,
            $price_markup_percent,
            $price_markup_nominal,
            $price_markdown_percent,
            $price_markdown_nominal,
            $round_on,
            $round_digit,
            $remarks,
            $cash_id,
        );

        $this->assertDatabaseHas('customer_groups', [
            'company_id' => $company_id,
            'cash_id' => $cash_id,
            'code' => $code,
            'name' => $name,
            'max_open_invoice' => $max_open_invoice,
            'max_outstanding_invoice' => $max_outstanding_invoice,
            'max_invoice_age' => $max_invoice_age,
            'payment_term' => $payment_term,
            'selling_point' => $selling_point,
            'selling_point_multiple' => $selling_point_multiple,
            'sell_at_cost' => $sell_at_cost,
            'price_markup_percent' => $price_markup_percent,
            'price_markup_nominal' => $price_markup_nominal,
            'price_markdown_percent' => $price_markdown_percent,
            'price_markdown_nominal' => $price_markdown_nominal,
            'round_on' => $round_on,
            'round_digit' => $round_digit,
            'remarks' => $remarks
        ]);
    }

    public function test_update()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;

        $faker = \Faker\Factory::create('id_ID');

        $this->cashService->create(
            $company_id,
            $faker->unique()->numberBetween(001, 99999),
            $faker->name(),
            (new RandomGenerator())->generateNumber(0, 1),
            (new RandomGenerator())->generateNumber(0, 1)
        );

        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $max_open_invoice = (new RandomGenerator())->generateNumber(1, 9999);
        $max_outstanding_invoice = (new RandomGenerator())->generateNumber(1, 9999);
        $max_invoice_age = (new RandomGenerator())->generateNumber(1, 9999);
        $payment_term = (new RandomGenerator())->generateNumber(1, 365);
        $selling_point = (new RandomGenerator())->generateNumber(1, 9999);
        $selling_point_multiple = (new RandomGenerator())->generateNumber(1, 9999);
        $sell_at_cost = (new RandomGenerator())->generateNumber(1, 9999);
        $price_markup_percent = (new RandomGenerator())->generateNumber(1, 9999);
        $price_markup_nominal = (new RandomGenerator())->generateNumber(1, 9999);
        $price_markdown_percent = (new RandomGenerator())->generateNumber(1, 9999);
        $price_markdown_nominal = (new RandomGenerator())->generateNumber(1, 9999);
        $round_on = (new RandomGenerator())->generateNumber(0, 2);
        $round_digit = (new RandomGenerator())->generateNumber(1, 9999);
        $remarks = null;
        $cash_id = Cash::inRandomOrder()->get()[0]->id;
        $response = $this->service->create(
            $company_id,
            $code,
            $name,
            $max_open_invoice,
            $max_outstanding_invoice,
            $max_invoice_age,
            $payment_term,
            $selling_point,
            $selling_point_multiple,
            $sell_at_cost,
            $price_markup_percent,
            $price_markup_nominal,
            $price_markdown_percent,
            $price_markdown_nominal,
            $round_on,
            $round_digit,
            $remarks,
            $cash_id
        );
        $rId = Hashids::decode($response)[0];

        $company_id_new = Company::inRandomOrder()->get()[0]->id;
        $code_new = (new RandomGenerator())->generateNumber(1, 9999);
        $name_new = $this->faker->name;
        $max_open_invoice_new = (new RandomGenerator())->generateNumber(1, 9999);
        $max_outstanding_invoice_new = (new RandomGenerator())->generateNumber(1, 9999);
        $max_invoice_age_new = (new RandomGenerator())->generateNumber(1, 9999);
        $payment_term_new = (new RandomGenerator())->generateNumber(1, 365);
        $selling_point_new = (new RandomGenerator())->generateNumber(1, 9999);
        $selling_point_multiple_new = (new RandomGenerator())->generateNumber(1, 9999);
        $sell_at_cost_new = (new RandomGenerator())->generateNumber(1, 9999);
        $price_markup_percent_new = (new RandomGenerator())->generateNumber(1, 9999);
        $price_markup_nominal_new = (new RandomGenerator())->generateNumber(1, 9999);
        $price_markdown_percent_new = (new RandomGenerator())->generateNumber(1, 9999);
        $price_markdown_nominal_new = (new RandomGenerator())->generateNumber(1, 9999);
        $round_on_new = (new RandomGenerator())->generateNumber(0, 2);
        $round_digit_new = (new RandomGenerator())->generateNumber(1, 9999);
        $remarks_new = null;
        $cash_id_new = Cash::inRandomOrder()->get()[0]->id;
        $response = $this->service->update(
            $rId, 
            $company_id_new,
            $code_new,
            $name_new,
            $max_open_invoice_new,
            $max_outstanding_invoice_new,
            $max_invoice_age_new,
            $payment_term_new,
            $selling_point_new,
            $selling_point_multiple_new,
            $sell_at_cost_new,
            $price_markup_percent_new,
            $price_markup_nominal_new,
            $price_markdown_percent_new,
            $price_markdown_nominal_new,
            $round_on_new,
            $round_digit_new,
            $remarks_new,
            $cash_id_new
        );

        $this->assertDatabaseHas('customer_groups', [
            'company_id' => $company_id_new,
            'cash_id' => $cash_id_new,
            'code' => $code_new,
            'name' => $name_new,
            'max_open_invoice' => $max_open_invoice_new,
            'max_outstanding_invoice' => $max_outstanding_invoice_new,
            'max_invoice_age' => $max_invoice_age_new,
            'payment_term' => $payment_term_new,
            'selling_point' => $selling_point_new,
            'selling_point_multiple' => $selling_point_multiple_new,
            'sell_at_cost' => $sell_at_cost_new,
            'price_markup_percent' => $price_markup_percent_new,
            'price_markup_nominal' => $price_markup_nominal_new,
            'price_markdown_percent' => $price_markdown_percent_new,
            'price_markdown_nominal' => $price_markdown_nominal_new,
            'round_on' => $round_on_new,
            'round_digit' => $round_digit_new,
            'remarks' => $remarks_new,
        ]);
    }

    public function test_delete()
    {
        $company_id = Company::inRandomOrder()->get()[0]->id;
        $code = (new RandomGenerator())->generateNumber(1, 9999);
        $name = $this->faker->name;
        $max_open_invoice = (new RandomGenerator())->generateNumber(1, 9999);
        $max_outstanding_invoice = (new RandomGenerator())->generateNumber(1, 9999);
        $max_invoice_age = (new RandomGenerator())->generateNumber(0, 1);
        $payment_term = (new RandomGenerator())->generateNumber(1, 365);
        $selling_point = (new RandomGenerator())->generateNumber(1, 9999);
        $selling_point_multiple = (new RandomGenerator())->generateNumber(1, 9999);
        $sell_at_cost = (new RandomGenerator())->generateNumber(1, 9999);
        $price_markup_percent = (new RandomGenerator())->generateNumber(1, 9999);
        $price_markup_nominal = (new RandomGenerator())->generateNumber(1, 9999);
        $price_markdown_percent = (new RandomGenerator())->generateNumber(1, 9999);
        $price_markdown_nominal = (new RandomGenerator())->generateNumber(1, 9999);
        $round_on = (new RandomGenerator())->generateNumber(0, 2);
        $round_digit = (new RandomGenerator())->generateNumber(1, 9999);
        $remarks = null;
        $cash_id = Cash::inRandomOrder()->get()[0]->id;
        $response = $this->service->create(
            $company_id,
            $code,
            $name,
            $max_open_invoice,
            $max_outstanding_invoice,
            $max_invoice_age,
            $payment_term,
            $selling_point,
            $selling_point_multiple,
            $sell_at_cost,
            $price_markup_percent,
            $price_markup_nominal,
            $price_markdown_percent,
            $price_markdown_nominal,
            $round_on,
            $round_digit,
            $remarks,
            $cash_id
        );
        $rId = Hashids::decode($response)[0];

        $response = $this->service->delete($rId);
        $deleted_at = CustomerGroup::withTrashed()->find($rId)->deleted_at->format('Y-m-d H:i:s');
        
        $this->assertDatabaseHas('customer_groups', [
            'id' => $rId,
            'deleted_at' => $deleted_at
        ]);
    }
}
