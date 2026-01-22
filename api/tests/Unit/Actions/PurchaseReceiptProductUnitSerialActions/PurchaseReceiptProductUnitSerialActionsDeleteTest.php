<?php

namespace Tests\Unit\Actions\PurchaseReceiptProductUnitSerialActions;

use App\Actions\PurchaseReceiptProductUnitSerial\PurchaseReceiptProductUnitSerialActions;
use App\Models\Company;
use App\Models\PurchaseReceiptProductUnitSerial;
use App\Models\User;
use Tests\ActionsTestCase;

class PurchaseReceiptProductUnitSerialActionsDeleteTest extends ActionsTestCase
{
    private PurchaseReceiptProductUnitSerialActions $purchaseReceiptProductUnitSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReceiptProductUnitSerialActions = new PurchaseReceiptProductUnitSerialActions();
    }

    public function test_purchase_receipt_product_unit_serial_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReceiptProductUnitSerial::factory())
            )->create();

        $purchaseReceiptProductUnitSerial = $user->companies()->inRandomOrder()->first()
            ->purchaseReceiptProductUnitSerials()->inRandomOrder()->first();
        $result = $this->purchaseReceiptProductUnitSerialActions->delete($purchaseReceiptProductUnitSerial);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('purchase_receipt_product_unit_serials', [
            'id' => $purchaseReceiptProductUnitSerial->id,
        ]);
    }
}
