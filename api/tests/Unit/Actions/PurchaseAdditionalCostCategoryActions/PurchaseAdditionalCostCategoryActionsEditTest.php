<?php

namespace Tests\Unit\Actions\PurchaseAdditionalCostCategoryActions;

use App\Actions\PurchaseAdditionalCostCategory\PurchaseAdditionalCostCategoryActions;
use App\Models\Company;
use App\Models\PurchaseAdditionalCostCategory;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseAdditionalCostCategoryActionsEditTest extends ActionsTestCase
{
    private PurchaseAdditionalCostCategoryActions $purchaseAdditionalCostCategoryActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseAdditionalCostCategoryActions = new PurchaseAdditionalCostCategoryActions();
    }

    public function test_purchase_additional_cost_category_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseAdditionalCostCategory::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseAdditionalCostCategory = $company->purchaseAdditionalCostCategories()->inRandomOrder()->first();

        $purchaseAdditionalCostCategoryArr = PurchaseAdditionalCostCategory::factory()->make()->toArray();

        $result = $this->purchaseAdditionalCostCategoryActions->update($purchaseAdditionalCostCategory, $purchaseAdditionalCostCategoryArr);

        $this->assertInstanceOf(PurchaseAdditionalCostCategory::class, $result);
        $this->assertDatabaseHas('purchase_additional_cost_categories', [
            'id' => $purchaseAdditionalCostCategory->id,
            'company_id' => $purchaseAdditionalCostCategory->company_id,
            'code' => $purchaseAdditionalCostCategoryArr['code'],
            'name' => $purchaseAdditionalCostCategoryArr['name'],
        ]);
    }

    public function test_purchase_additional_cost_category_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseAdditionalCostCategory::factory())
            )->create();

        $purchaseAdditionalCostCategory = $user->companies()->inRandomOrder()->first()
            ->purchaseAdditionalCostCategories()->inRandomOrder()->first();

        $purchaseAdditionalCostCategoryArr = [];

        $this->purchaseAdditionalCostCategoryActions->update($purchaseAdditionalCostCategory, $purchaseAdditionalCostCategoryArr);
    }
}
