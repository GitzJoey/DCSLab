<?php

namespace Tests\Unit\Actions\PurchaseReturnAdditionalCostCategoryActions;

use App\Actions\PurchaseReturnAdditionalCostCategory\PurchaseReturnAdditionalCostCategoryActions;
use App\Models\Company;
use App\Models\PurchaseReturnAdditionalCostCategory;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseReturnAdditionalCostCategoryActionsCreateTest extends ActionsTestCase
{
    private PurchaseReturnAdditionalCostCategoryActions $purchaseReturnAdditionalCostCategoryActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReturnAdditionalCostCategoryActions = new PurchaseReturnAdditionalCostCategoryActions();
    }

    public function test_purchase_return_additional_cost_category_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $purchaseReturnAdditionalCostCategoryArr = PurchaseReturnAdditionalCostCategory::factory()->for($company)
            ->make()->toArray();

        $result = $this->purchaseReturnAdditionalCostCategoryActions->create($purchaseReturnAdditionalCostCategoryArr);

        $this->assertDatabaseHas('purchase_return_additional_cost_categories', [
            'id' => $result->id,
            'company_id' => $purchaseReturnAdditionalCostCategoryArr['company_id'],
            'code' => $purchaseReturnAdditionalCostCategoryArr['code'],
            'name' => $purchaseReturnAdditionalCostCategoryArr['name'],
        ]);
    }

    public function test_purchase_return_additional_cost_category_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->purchaseReturnAdditionalCostCategoryActions->create([]);
    }
}
