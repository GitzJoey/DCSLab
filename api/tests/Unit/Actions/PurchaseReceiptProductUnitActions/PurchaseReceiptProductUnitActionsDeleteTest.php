<?php

namespace Tests\Unit\Actions\PurchaseReceiptProductUnitActions;

use App\Actions\PurchaseReceiptProductUnit\PurchaseReceiptProductUnitActions;
use App\Models\Company;
use App\Models\PurchaseReceiptProductUnit;
use App\Models\User;
use Tests\ActionsTestCase;

class PurchaseReceiptProductUnitActionsDeleteTest extends ActionsTestCase
{
    private PurchaseReceiptProductUnitActions $purchaseReceiptProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReceiptProductUnitActions = new PurchaseReceiptProductUnitActions();
    }

    public function test_purchase_receipt_product_unit_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReceiptProductUnit::factory())
            )->create();

        $purchaseReceiptProductUnit = $user->companies()->inRandomOrder()->first()
            ->purchaseReceiptProductUnits()->inRandomOrder()->first();
        $result = $this->purchaseReceiptProductUnitActions->delete($purchaseReceiptProductUnit);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('purchase_receipt_product_units', [
            'id' => $purchaseReceiptProductUnit->id,
        ]);
    }
}
