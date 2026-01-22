<?php

namespace Tests\Unit\Actions\PurchaseAdditionalCostCategoryActions;

use App\Actions\PurchaseAdditionalCostCategory\PurchaseAdditionalCostCategoryActions;
use App\Models\Company;
use App\Models\PurchaseAdditionalCostCategory;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseAdditionalCostCategoryActionsCreateTest extends ActionsTestCase
{
    private PurchaseAdditionalCostCategoryActions $purchaseAdditionalCostCategoryActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseAdditionalCostCategoryActions = new PurchaseAdditionalCostCategoryActions();
    }

    public function test_purchase_additional_cost_category_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $purchaseAdditionalCostCategoryArr = PurchaseAdditionalCostCategory::factory()->for($company)
            ->make()->toArray();

        $result = $this->purchaseAdditionalCostCategoryActions->create($purchaseAdditionalCostCategoryArr);

        $this->assertDatabaseHas('purchase_additional_cost_categories', [
            'id' => $result->id,
            'company_id' => $purchaseAdditionalCostCategoryArr['company_id'],
            'code' => $purchaseAdditionalCostCategoryArr['code'],
            'name' => $purchaseAdditionalCostCategoryArr['name'],
        ]);
    }

    public function test_purchase_additional_cost_category_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->purchaseAdditionalCostCategoryActions->create([]);
    }
}
