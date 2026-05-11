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

        $customerGroupArr = CustomerGroup::factory()->make()->toArray();

        $result = $this->customerGroupActions->update($customerGroup, new \App\DTOs\CustomerGroupUpdateDTO(
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

        $this->assertInstanceOf(CustomerGroup::class, $result);
        $this->assertDatabaseHas('customer_groups', [
            'id' => $customerGroup->id,
            'company_id' => $customerGroup->company_id,
            'code' => $customerGroupArr['code'],
            'name' => $customerGroupArr['name'],
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

        $customerGroupArr = [];

        $this->customerGroupActions->update($customerGroup, new \App\DTOs\CustomerGroupUpdateDTO());
    }
}
