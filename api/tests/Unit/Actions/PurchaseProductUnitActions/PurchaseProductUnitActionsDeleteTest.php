<?php

namespace Tests\Unit\Actions\PurchaseProductUnitActions;

use App\Actions\PurchaseProductUnit\PurchaseProductUnitActions;
use App\Models\Company;
use App\Models\PurchaseProductUnit;
use App\Models\User;
use Tests\ActionsTestCase;

class PurchaseProductUnitActionsDeleteTest extends ActionsTestCase
{
    private PurchaseProductUnitActions $purchaseProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseProductUnitActions = new PurchaseProductUnitActions();
    }

    public function test_purchase_product_unit_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseProductUnit::factory())
            )->create();

        $purchaseProductUnit = $user->companies()->inRandomOrder()->first()
            ->purchaseProductUnits()->inRandomOrder()->first();
        $result = $this->purchaseProductUnitActions->delete($purchaseProductUnit);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('purchase_product_units', [
            'id' => $purchaseProductUnit->id,
        ]);
    }
}
