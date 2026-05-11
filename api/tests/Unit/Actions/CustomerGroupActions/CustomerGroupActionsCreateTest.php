<?php

namespace Tests\Unit\Actions\CustomerGroupActions;

use App\Actions\CustomerGroup\CustomerGroupActions;
use App\Models\Company;
use App\Models\CustomerGroup;
use App\Models\User;
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
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $customerGroupArr = CustomerGroup::factory()->for($company)
            ->make()->toArray();

        $result = $this->customerGroupActions->create(new \App\DTOs\CustomerGroupCreateDTO(
            companyId: $customerGroupArr['company_id'],
            code: $customerGroupArr['code'],
            name: $customerGroupArr['name'],
            paymentTermType: $customerGroupArr['payment_term_type'],
            paymentTerm: $customerGroupArr['payment_term'],
            sellAtCost: $customerGroupArr['sell_at_cost'],
            sellingPoint: $customerGroupArr['selling_point'],
            sellingPointMultiple: $customerGroupArr['selling_point_multiple'],
            priceMarkupPercent: $customerGroupArr['price_markup_percent'],
            priceMarkupNominal: $customerGroupArr['price_markup_nominal'],
            priceMarkdownPercent: $customerGroupArr['price_markdown_percent'],
            priceMarkdownNominal: $customerGroupArr['price_markdown_nominal'],
            roundingType: $customerGroupArr['rounding_type'],
            roundingDigit: $customerGroupArr['rounding_digit'],
            maxOpenInvoice: $customerGroupArr['max_open_invoice'],
            maxInvoiceAge: $customerGroupArr['max_invoice_age'],
            maxOutstandingInvoice: $customerGroupArr['max_outstanding_invoice'],
            remarks: $customerGroupArr['remarks']
        ));

        $this->assertDatabaseHas('customer_groups', [
            'id' => $result->id,
            'company_id' => $customerGroupArr['company_id'],
            'code' => $customerGroupArr['code'],
            'name' => $customerGroupArr['name'],
        ]);
    }

    public function test_customer_group_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->customerGroupActions->create(new \App\DTOs\CustomerGroupCreateDTO());

    }
}
