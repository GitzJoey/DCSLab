<?php

namespace Tests\Unit\Actions\CustomerActions;

use App\Actions\Customer\CustomerActions;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class CustomerActionsEditTest extends ActionsTestCase
{
    private CustomerActions $customerActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerActions = new CustomerActions();
    }

    public function test_customer_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Customer::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customer = $company->customers()->inRandomOrder()->first();

        $group = CustomerGroup::factory()->for($company)->create();

        $customerArr = Customer::factory()->for($company)
            ->make([
                'group_id' => $group->id,
            ])->toArray();

        $result = $this->customerActions->update($customer, $customerArr);

        $this->assertInstanceOf(Customer::class, $result);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'company_id' => $customer->company_id,
            'code' => $customerArr['code'],
            'is_member' => $customerArr['is_member'],
            'name' => $customerArr['name'],
            'group_id' => $customerArr['group_id'],
            'zone' => $customerArr['zone'],
            'max_open_invoice' => $customerArr['max_open_invoice'],
            'max_outstanding_invoice' => $customerArr['max_outstanding_invoice'],
            'max_invoice_age' => $customerArr['max_invoice_age'],
            'payment_term_type' => $customerArr['payment_term_type'],
            'payment_term' => $customerArr['payment_term'],
            'taxable_enterprise' => $customerArr['taxable_enterprise'],
            'tax_id' => $customerArr['tax_id'],
            'status' => $customerArr['status'],
            'remarks' => $customerArr['remarks'],
        ]);
    }

    public function test_customer_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Customer::factory())
            )->create();

        $customer = $user->companies()->inRandomOrder()->first()
            ->customers()->inRandomOrder()->first();

        $customerArr = [];

        $this->customerActions->update($customer, $customerArr);
    }
}
