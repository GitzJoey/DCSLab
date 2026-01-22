<?php

namespace Tests\Unit\Actions\PurchaseAdditionalCostCategoryActions;

use App\Actions\PurchaseAdditionalCostCategory\PurchaseAdditionalCostCategoryActions;
use App\Models\Company;
use App\Models\PurchaseAdditionalCostCategory;
use App\Models\User;
use Tests\ActionsTestCase;

class PurchaseAdditionalCostCategoryActionsDeleteTest extends ActionsTestCase
{
    private PurchaseAdditionalCostCategoryActions $purchaseAdditionalCostCategoryActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseAdditionalCostCategoryActions = new PurchaseAdditionalCostCategoryActions();
    }

    public function test_purchase_additional_cost_category_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseAdditionalCostCategory::factory())
            )->create();

        $purchaseAdditionalCostCategory = $user->companies()->inRandomOrder()->first()
            ->purchaseAdditionalCostCategories()->inRandomOrder()->first();
        $result = $this->purchaseAdditionalCostCategoryActions->delete($purchaseAdditionalCostCategory);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('purchase_additional_cost_categories', [
            'id' => $purchaseAdditionalCostCategory->id,
        ]);
    }
}
