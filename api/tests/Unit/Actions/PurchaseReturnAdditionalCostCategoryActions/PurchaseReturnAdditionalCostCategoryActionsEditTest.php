<?php

namespace Tests\Unit\Actions\PurchaseReturnAdditionalCostCategoryActions;

use App\Actions\PurchaseReturnAdditionalCostCategory\PurchaseReturnAdditionalCostCategoryActions;
use App\Models\Company;
use App\Models\PurchaseReturnAdditionalCostCategory;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseReturnAdditionalCostCategoryActionsEditTest extends ActionsTestCase
{
    private PurchaseReturnAdditionalCostCategoryActions $purchaseReturnAdditionalCostCategoryActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReturnAdditionalCostCategoryActions = new PurchaseReturnAdditionalCostCategoryActions();
    }

    public function test_purchase_return_additional_cost_category_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReturnAdditionalCostCategory::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseReturnAdditionalCostCategory = $company->purchaseReturnAdditionalCostCategories()->inRandomOrder()->first();

        $purchaseReturnAdditionalCostCategoryArr = PurchaseReturnAdditionalCostCategory::factory()->make()->toArray();

        $result = $this->purchaseReturnAdditionalCostCategoryActions->update($purchaseReturnAdditionalCostCategory, $purchaseReturnAdditionalCostCategoryArr);

        $this->assertInstanceOf(PurchaseReturnAdditionalCostCategory::class, $result);
        $this->assertDatabaseHas('purchase_return_additional_cost_categories', [
            'id' => $purchaseReturnAdditionalCostCategory->id,
            'company_id' => $purchaseReturnAdditionalCostCategory->company_id,
            'code' => $purchaseReturnAdditionalCostCategoryArr['code'],
            'name' => $purchaseReturnAdditionalCostCategoryArr['name'],
        ]);
    }

    public function test_purchase_return_additional_cost_category_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReturnAdditionalCostCategory::factory())
            )->create();

        $purchaseReturnAdditionalCostCategory = $user->companies()->inRandomOrder()->first()
            ->purchaseReturnAdditionalCostCategories()->inRandomOrder()->first();

        $purchaseReturnAdditionalCostCategoryArr = [];

        $this->purchaseReturnAdditionalCostCategoryActions->update($purchaseReturnAdditionalCostCategory, $purchaseReturnAdditionalCostCategoryArr);
    }
}
