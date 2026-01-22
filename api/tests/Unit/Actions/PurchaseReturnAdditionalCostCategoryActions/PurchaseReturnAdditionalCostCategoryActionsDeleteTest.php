<?php

namespace Tests\Unit\Actions\PurchaseReturnAdditionalCostCategoryActions;

use App\Actions\PurchaseReturnAdditionalCostCategory\PurchaseReturnAdditionalCostCategoryActions;
use App\Models\Company;
use App\Models\PurchaseReturnAdditionalCostCategory;
use App\Models\User;
use Tests\ActionsTestCase;

class PurchaseReturnAdditionalCostCategoryActionsDeleteTest extends ActionsTestCase
{
    private PurchaseReturnAdditionalCostCategoryActions $purchaseReturnAdditionalCostCategoryActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReturnAdditionalCostCategoryActions = new PurchaseReturnAdditionalCostCategoryActions();
    }

    public function test_purchase_return_additional_cost_category_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReturnAdditionalCostCategory::factory())
            )->create();

        $purchaseReturnAdditionalCostCategory = $user->companies()->inRandomOrder()->first()
            ->purchaseReturnAdditionalCostCategories()->inRandomOrder()->first();
        $result = $this->purchaseReturnAdditionalCostCategoryActions->delete($purchaseReturnAdditionalCostCategory);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('purchase_return_additional_cost_categories', [
            'id' => $purchaseReturnAdditionalCostCategory->id,
        ]);
    }
}
