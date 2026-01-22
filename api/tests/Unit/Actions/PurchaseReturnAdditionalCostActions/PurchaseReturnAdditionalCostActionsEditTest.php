<?php

namespace Tests\Unit\Actions\PurchaseReturnAdditionalCostActions;

use App\Actions\PurchaseReturnAdditionalCost\PurchaseReturnAdditionalCostActions;
use App\Models\Company;
use App\Models\PurchaseReturnAdditionalCost;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseReturnAdditionalCostActionsEditTest extends ActionsTestCase
{
    private PurchaseReturnAdditionalCostActions $purchaseReturnAdditionalCostActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReturnAdditionalCostActions = new PurchaseReturnAdditionalCostActions();
    }

    public function test_purchase_return_additional_cost_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReturnAdditionalCost::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseReturnAdditionalCost = $company->purchaseReturnAdditionalCosts()->inRandomOrder()->first();

        $purchaseReturnAdditionalCostArr = PurchaseReturnAdditionalCost::factory()->make()->toArray();

        $result = $this->purchaseReturnAdditionalCostActions->update($purchaseReturnAdditionalCost, $purchaseReturnAdditionalCostArr);

        $this->assertInstanceOf(PurchaseReturnAdditionalCost::class, $result);
        $this->assertDatabaseHas('purchase_return_additional_costs', [
            'id' => $purchaseReturnAdditionalCost->id,
            'company_id' => $purchaseReturnAdditionalCost->company_id,
            'code' => $purchaseReturnAdditionalCostArr['code'],
            'name' => $purchaseReturnAdditionalCostArr['name'],
        ]);
    }

    public function test_purchase_return_additional_cost_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReturnAdditionalCost::factory())
            )->create();

        $purchaseReturnAdditionalCost = $user->companies()->inRandomOrder()->first()
            ->purchaseReturnAdditionalCosts()->inRandomOrder()->first();

        $purchaseReturnAdditionalCostArr = [];

        $this->purchaseReturnAdditionalCostActions->update($purchaseReturnAdditionalCost, $purchaseReturnAdditionalCostArr);
    }
}
