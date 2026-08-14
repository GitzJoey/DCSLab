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

        $payload = CustomerGroup::factory()->for($company)
            ->make()->toArray();

        $dto = new \App\DTOs\CustomerGroupCreateDTO(
            companyId: $payload['company_id'],
            code: $payload['code'],
            name: $payload['name'],
            paymentTermType: $payload['payment_term_type'],
            paymentTerm: $payload['payment_term'],
            sellAtCost: $payload['sell_at_cost'],
            sellingPoint: $payload['selling_point'],
            sellingPointMultiple: $payload['selling_point_multiple'],
            priceMarkupPercent: $payload['price_markup_percent'],
            priceMarkupNominal: $payload['price_markup_nominal'],
            priceMarkdownPercent: $payload['price_markdown_percent'],
            priceMarkdownNominal: $payload['price_markdown_nominal'],
            roundingType: $payload['rounding_type'],
            roundingDigit: $payload['rounding_digit'],
            maxOpenInvoice: $payload['max_open_invoice'],
            maxInvoiceAge: $payload['max_invoice_age'],
            maxOutstandingInvoice: $payload['max_outstanding_invoice'],
            remarks: $payload['remarks']
        );

        $result = $this->customerGroupActions->create($dto);
        $this->assertDatabaseHas('customer_groups', [
            'id' => $result->id,
            'company_id' => $payload['company_id'],
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_customer_group_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $dto = new \App\DTOs\CustomerGroupCreateDTO();

        $this->customerGroupActions->create($dto);
    }
}
