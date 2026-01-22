<?php

namespace Tests\Unit\Actions\PurchaseOrderProductUnitActions;

use App\Actions\PurchaseOrderProductUnit\PurchaseOrderProductUnitActions;
use App\Models\Company;
use App\Models\PurchaseOrderProductUnit;
use App\Models\User;
use Tests\ActionsTestCase;

class PurchaseOrderProductUnitActionsDeleteTest extends ActionsTestCase
{
    private PurchaseOrderProductUnitActions $purchaseOrderProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseOrderProductUnitActions = new PurchaseOrderProductUnitActions();
    }

    public function test_purchase_order_product_unit_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseOrderProductUnit::factory())
            )->create();

        $purchaseOrderProductUnit = $user->companies()->inRandomOrder()->first()
            ->purchaseOrderProductUnits()->inRandomOrder()->first();
        $result = $this->purchaseOrderProductUnitActions->delete($purchaseOrderProductUnit);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('purchase_order_product_units', [
            'id' => $purchaseOrderProductUnit->id,
        ]);
    }
}
