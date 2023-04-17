<?php

namespace Tests\Feature;

use App\Actions\CustomerGroup\CustomerGroupActions;
use App\Models\Company;
use App\Models\CustomerGroup;
use App\Models\User;
use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\CustomerGroupTableSeeder;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerGroupActionsEditTest extends TestCase
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

    public function test_customer_group_actions_call_update_expect_db_updated()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->customerGroupSeeder->callWith(CustomerGroupTableSeeder::class, [3, $companyId]);

        $customerGroup = $user->companies->first()->customerGroups->first();
        $customerGroupArr = CustomerGroup::factory()->make();

        $result = $this->customerGroupActions->update($customerGroup, $customerGroupArr->toArray());

        $this->assertInstanceOf(CustomerGroup::class, $result);
        $this->assertDatabaseHas('customer_groups', [
            'id' => $customerGroup->id,
            'company_id' => $customerGroup->company_id,
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

    public function test_customer_group_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
                    ->has(Company::factory(2)->setIsDefault()
                        ->has(CustomerGroup::factory(), 'customer_groups'), 'companies')
                    ->create();

        $customerGroup = $user->companies->first()->customer_groups->first();
        $customerGroupArr = [];

        $this->customerGroupActions->update($customerGroup, $customerGroupArr);
    }
}
