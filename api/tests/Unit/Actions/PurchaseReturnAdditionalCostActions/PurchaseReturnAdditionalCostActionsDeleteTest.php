<?php

namespace Tests\Unit\Actions\PurchaseReturnAdditionalCostActions;

use App\Actions\PurchaseReturnAdditionalCost\PurchaseReturnAdditionalCostActions;
use App\Models\Company;
use App\Models\PurchaseReturnAdditionalCost;
use App\Models\User;
use Tests\ActionsTestCase;

class PurchaseReturnAdditionalCostActionsDeleteTest extends ActionsTestCase
{
    private PurchaseReturnAdditionalCostActions $purchaseReturnAdditionalCostActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReturnAdditionalCostActions = new PurchaseReturnAdditionalCostActions();
    }

    public function test_purchase_return_additional_cost_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReturnAdditionalCost::factory())
            )->create();

        $purchaseReturnAdditionalCost = $user->companies()->inRandomOrder()->first()
            ->purchaseReturnAdditionalCosts()->inRandomOrder()->first();
        $result = $this->purchaseReturnAdditionalCostActions->delete($purchaseReturnAdditionalCost);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('purchase_return_additional_costs', [
            'id' => $purchaseReturnAdditionalCost->id,
        ]);
    }
}
