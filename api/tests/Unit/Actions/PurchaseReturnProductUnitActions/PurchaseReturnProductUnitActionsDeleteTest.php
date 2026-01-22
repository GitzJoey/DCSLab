<?php

namespace Tests\Unit\Actions\PurchaseReturnProductUnitActions;

use App\Actions\PurchaseReturnProductUnit\PurchaseReturnProductUnitActions;
use App\Models\Company;
use App\Models\PurchaseReturnProductUnit;
use App\Models\User;
use Tests\ActionsTestCase;

class PurchaseReturnProductUnitActionsDeleteTest extends ActionsTestCase
{
    private PurchaseReturnProductUnitActions $purchaseReturnProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReturnProductUnitActions = new PurchaseReturnProductUnitActions();
    }

    public function test_purchase_return_product_unit_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReturnProductUnit::factory())
            )->create();

        $purchaseReturnProductUnit = $user->companies()->inRandomOrder()->first()
            ->purchaseReturnProductUnits()->inRandomOrder()->first();
        $result = $this->purchaseReturnProductUnitActions->delete($purchaseReturnProductUnit);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('purchase_return_product_units', [
            'id' => $purchaseReturnProductUnit->id,
        ]);
    }
}
