<?php

namespace Tests\Unit\Actions\PurchaseReturnProductUnitSerialActions;

use App\Actions\PurchaseReturnProductUnitSerial\PurchaseReturnProductUnitSerialActions;
use App\Models\Company;
use App\Models\PurchaseReturnProductUnitSerial;
use App\Models\User;
use Tests\ActionsTestCase;

class PurchaseReturnProductUnitSerialActionsDeleteTest extends ActionsTestCase
{
    private PurchaseReturnProductUnitSerialActions $purchaseReturnProductUnitSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReturnProductUnitSerialActions = new PurchaseReturnProductUnitSerialActions();
    }

    public function test_purchase_return_product_unit_serial_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReturnProductUnitSerial::factory())
            )->create();

        $purchaseReturnProductUnitSerial = $user->companies()->inRandomOrder()->first()
            ->purchaseReturnProductUnitSerials()->inRandomOrder()->first();
        $result = $this->purchaseReturnProductUnitSerialActions->delete($purchaseReturnProductUnitSerial);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('purchase_return_product_unit_serials', [
            'id' => $purchaseReturnProductUnitSerial->id,
        ]);
    }
}
