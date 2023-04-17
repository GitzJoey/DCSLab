<?php

namespace Tests\Feature;

use App\Actions\CustomerGroup\CustomerGroupActions;
use App\Models\CustomerGroup;
use App\Models\User;
use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\CustomerGroupTableSeeder;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerGroupActionsCreateTest extends TestCase
{
    use WithFaker;

    private $customerGroupActions;

    private $companySeeder;

    private $customerGroupSeeder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerGroupActions = app(CustomerGroupActions::class);

        $this->companySeeder = new CompanyTableSeeder();
        $this->customerGroupSeeder = new CustomerGroupTableSeeder();
    }

    public function test_customer_group_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $customerGroupArr = CustomerGroup::factory()->make([
            'company_id' => $companyId,
        ]);

        $result = $this->customerGroupActions->create($customerGroupArr->toArray());

        $this->assertDatabaseHas('customer_groups', [
            'id' => $result->id,
            'company_id' => $customerGroupArr['company_id'],
            'code' => $customerGroupArr['code'],
            'name' => $customerGroupArr['name'],
            'max_open_invoice' => $customerGroupArr['max_open_invoice'],
            'max_outstanding_invoice' => $customerGroupArr['max_outstanding_invoice'],
            'max_invoice_age' => $customerGroupArr['max_invoice_age'],
            'payment_term_type' => $customerGroupArr['payment_term_type'],
            'payment_term' => $customerGroupArr['payment_term'],
            'selling_point' => $customerGroupArr['selling_point'],
            'selling_point_multiple' => $customerGroupArr['selling_point_multiple'],
            'sell_at_cost' => $customerGroupArr['sell_at_cost'],
            'price_markup_percent' => $customerGroupArr['price_markup_percent'],
            'price_markup_nominal' => $customerGroupArr['price_markup_nominal'],
            'price_markdown_percent' => $customerGroupArr['price_markdown_percent'],
            'price_markdown_nominal' => $customerGroupArr['price_markdown_nominal'],
            'round_on' => $customerGroupArr['round_on'],
            'round_digit' => $customerGroupArr['round_digit'],
            'remarks' => $customerGroupArr['remarks'],
        ]);
    }

    public function test_customer_group_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->customerGroupActions->create([]);
    }
}
