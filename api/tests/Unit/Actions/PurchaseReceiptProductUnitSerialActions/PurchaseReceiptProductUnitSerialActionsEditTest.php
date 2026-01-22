<?php

namespace Tests\Unit\Actions\PurchaseReceiptProductUnitSerialActions;

use App\Actions\PurchaseReceiptProductUnitSerial\PurchaseReceiptProductUnitSerialActions;
use App\Models\Company;
use App\Models\PurchaseReceiptProductUnitSerial;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseReceiptProductUnitSerialActionsEditTest extends ActionsTestCase
{
    private PurchaseReceiptProductUnitSerialActions $purchaseReceiptProductUnitSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReceiptProductUnitSerialActions = new PurchaseReceiptProductUnitSerialActions();
    }

    public function test_purchase_receipt_product_unit_serial_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReceiptProductUnitSerial::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseReceiptProductUnitSerial = $company->purchaseReceiptProductUnitSerials()->inRandomOrder()->first();

        $purchaseReceiptProductUnitSerialArr = PurchaseReceiptProductUnitSerial::factory()->make()->toArray();

        $result = $this->purchaseReceiptProductUnitSerialActions->update($purchaseReceiptProductUnitSerial, $purchaseReceiptProductUnitSerialArr);

        $this->assertInstanceOf(PurchaseReceiptProductUnitSerial::class, $result);
        $this->assertDatabaseHas('purchase_receipt_product_unit_serials', [
            'id' => $purchaseReceiptProductUnitSerial->id,
            'company_id' => $purchaseReceiptProductUnitSerial->company_id,
            'code' => $purchaseReceiptProductUnitSerialArr['code'],
            'name' => $purchaseReceiptProductUnitSerialArr['name'],
        ]);
    }

    public function test_purchase_receipt_product_unit_serial_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReceiptProductUnitSerial::factory())
            )->create();

        $purchaseReceiptProductUnitSerial = $user->companies()->inRandomOrder()->first()
            ->purchaseReceiptProductUnitSerials()->inRandomOrder()->first();

        $purchaseReceiptProductUnitSerialArr = [];

        $this->purchaseReceiptProductUnitSerialActions->update($purchaseReceiptProductUnitSerial, $purchaseReceiptProductUnitSerialArr);
    }
}
