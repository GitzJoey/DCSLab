<?php

namespace Tests\Unit\Actions\PurchaseAdditionalCostActions;

use App\Actions\PurchaseAdditionalCost\PurchaseAdditionalCostActions;
use App\Models\Company;
use App\Models\PurchaseAdditionalCost;
use App\Models\User;
use Tests\ActionsTestCase;

class PurchaseAdditionalCostActionsDeleteTest extends ActionsTestCase
{
    private PurchaseAdditionalCostActions $purchaseAdditionalCostActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseAdditionalCostActions = new PurchaseAdditionalCostActions();
    }

    public function test_purchase_additional_cost_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseAdditionalCost::factory())
            )->create();

        $purchaseAdditionalCost = $user->companies()->inRandomOrder()->first()
            ->purchaseAdditionalCosts()->inRandomOrder()->first();
        $result = $this->purchaseAdditionalCostActions->delete($purchaseAdditionalCost);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('purchase_additional_costs', [
            'id' => $purchaseAdditionalCost->id,
        ]);
    }
}
