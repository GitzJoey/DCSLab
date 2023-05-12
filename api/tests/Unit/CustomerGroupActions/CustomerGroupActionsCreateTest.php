<?php

namespace Tests\Unit\CustomerGroupActions;

use App\Actions\CustomerGroup\CustomerGroupActions;
use App\Models\Company;
use App\Models\CustomerGroup;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class CustomerGroupActionsCreateTest extends ActionsTestCase
{
    private CustomerGroupActions $customerGroupActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerGroupActions = new CustomerGroupActions();
    }

    public function test_customer_group_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $customerGroupArr = CustomerGroup::factory()->for($company)->make()->toArray();

        $result = $this->customerGroupActions->create($customerGroupArr);

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
