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

        $payload = Customer::factory()->for($company)
            ->make([
                'group_id' => $group->id,
            ])->toArray();

        $dto = new \App\DTOs\CustomerUpdateDTO(
            groupId: $payload['group_id'],
            code: $payload['code'],
            name: $payload['name'],
            paymentTermType: $payload['payment_term_type'],
            paymentTerm: $payload['payment_term'],
            taxableEnterprise: $payload['taxable_enterprise'],
            taxId: $payload['tax_id'],
            isMember: $payload['is_member'],
            maxOpenInvoice: $payload['max_open_invoice'],
            maxInvoiceAge: $payload['max_invoice_age'],
            maxOutstandingInvoice: $payload['max_outstanding_invoice'],
            zone: $payload['zone'],
            remarks: $payload['remarks'],
            status: $payload['status']
        );

        $result = $this->customerActions->update($customer, $dto);
        $this->assertInstanceOf(Customer::class, $result);
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'company_id' => $customer->company_id,
            'code' => $payload['code'],
            'is_member' => $payload['is_member'],
            'name' => $payload['name'],
            'group_id' => $payload['group_id'],
            'zone' => $payload['zone'],
            'max_open_invoice' => $payload['max_open_invoice'],
            'max_outstanding_invoice' => $payload['max_outstanding_invoice'],
            'max_invoice_age' => $payload['max_invoice_age'],
            'payment_term_type' => $payload['payment_term_type'],
            'payment_term' => $payload['payment_term'],
            'taxable_enterprise' => $payload['taxable_enterprise'],
            'tax_id' => $payload['tax_id'],
            'status' => $payload['status'],
            'remarks' => $payload['remarks'],
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

        $payload = [];

        $dto = new \App\DTOs\CustomerUpdateDTO();

        $this->customerActions->update($customer, $dto);
    }
}
