<?php

namespace Tests\Unit\Actions\PurchaseProductUnitSerialActions;

use App\Actions\PurchaseProductUnitSerial\PurchaseProductUnitSerialActions;
use App\Models\Company;
use App\Models\PurchaseProductUnitSerial;
use App\Models\User;
use Tests\ActionsTestCase;

class PurchaseProductUnitSerialActionsDeleteTest extends ActionsTestCase
{
    private PurchaseProductUnitSerialActions $purchaseProductUnitSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseProductUnitSerialActions = new PurchaseProductUnitSerialActions();
    }

    public function test_purchase_product_unit_serial_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseProductUnitSerial::factory())
            )->create();

        $purchaseProductUnitSerial = $user->companies()->inRandomOrder()->first()
            ->purchaseProductUnitSerials()->inRandomOrder()->first();
        $result = $this->purchaseProductUnitSerialActions->delete($purchaseProductUnitSerial);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('purchase_product_unit_serials', [
            'id' => $purchaseProductUnitSerial->id,
        ]);
    }
}
