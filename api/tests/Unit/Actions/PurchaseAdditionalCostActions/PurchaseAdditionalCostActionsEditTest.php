<?php

namespace Tests\Unit\Actions\PurchaseAdditionalCostActions;

use App\Actions\PurchaseAdditionalCost\PurchaseAdditionalCostActions;
use App\Models\Company;
use App\Models\PurchaseAdditionalCost;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseAdditionalCostActionsEditTest extends ActionsTestCase
{
    private PurchaseAdditionalCostActions $purchaseAdditionalCostActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseAdditionalCostActions = new PurchaseAdditionalCostActions();
    }

    public function test_purchase_additional_cost_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseAdditionalCost::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseAdditionalCost = $company->purchaseAdditionalCosts()->inRandomOrder()->first();

        $purchaseAdditionalCostArr = PurchaseAdditionalCost::factory()->make()->toArray();

        $result = $this->purchaseAdditionalCostActions->update($purchaseAdditionalCost, $purchaseAdditionalCostArr);

        $this->assertInstanceOf(PurchaseAdditionalCost::class, $result);
        $this->assertDatabaseHas('purchase_additional_costs', [
            'id' => $purchaseAdditionalCost->id,
            'company_id' => $purchaseAdditionalCost->company_id,
            'code' => $purchaseAdditionalCostArr['code'],
            'name' => $purchaseAdditionalCostArr['name'],
        ]);
    }

    public function test_purchase_additional_cost_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseAdditionalCost::factory())
            )->create();

        $purchaseAdditionalCost = $user->companies()->inRandomOrder()->first()
            ->purchaseAdditionalCosts()->inRandomOrder()->first();

        $purchaseAdditionalCostArr = [];

        $this->purchaseAdditionalCostActions->update($purchaseAdditionalCost, $purchaseAdditionalCostArr);
    }
}
