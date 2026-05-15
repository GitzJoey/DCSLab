<?php

namespace Tests\Unit\Actions\CustomerGroupActions;

use App\Actions\CustomerGroup\CustomerGroupActions;
use App\Models\Company;
use App\Models\CustomerGroup;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class CustomerGroupActionsEditTest extends ActionsTestCase
{
    private CustomerGroupActions $customerGroupActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerGroupActions = new CustomerGroupActions();
    }

    public function test_customer_group_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(CustomerGroup::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        $payload = CustomerGroup::factory()->make()->toArray();

        $dto = new \App\DTOs\CustomerGroupUpdateDTO(
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

        $result = $this->customerGroupActions->update($customerGroup, $dto);
        $this->assertInstanceOf(CustomerGroup::class, $result);
        $this->assertDatabaseHas('customer_groups', [
            'id' => $customerGroup->id,
            'company_id' => $customerGroup->company_id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_customer_group_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(CustomerGroup::factory())
            )->create();

        $customerGroup = $user->companies()->inRandomOrder()->first()
            ->customerGroups()->inRandomOrder()->first();

        $payload = [];

        $dto = new \App\DTOs\CustomerGroupUpdateDTO();

        $this->customerGroupActions->update($customerGroup, $dto);
    }
}
