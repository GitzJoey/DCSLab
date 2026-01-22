<?php

namespace Tests\Unit\Actions\PurchaseReceiptProductUnitSerialActions;

use App\Actions\PurchaseReceiptProductUnitSerial\PurchaseReceiptProductUnitSerialActions;
use App\Models\Company;
use App\Models\PurchaseReceiptProductUnitSerial;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseReceiptProductUnitSerialActionsCreateTest extends ActionsTestCase
{
    private PurchaseReceiptProductUnitSerialActions $purchaseReceiptProductUnitSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReceiptProductUnitSerialActions = new PurchaseReceiptProductUnitSerialActions();
    }

    public function test_purchase_receipt_product_unit_serial_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $purchaseReceiptProductUnitSerialArr = PurchaseReceiptProductUnitSerial::factory()->for($company)
            ->make()->toArray();

        $result = $this->purchaseReceiptProductUnitSerialActions->create($purchaseReceiptProductUnitSerialArr);

        $this->assertDatabaseHas('purchase_receipt_product_unit_serials', [
            'id' => $result->id,
            'company_id' => $purchaseReceiptProductUnitSerialArr['company_id'],
            'code' => $purchaseReceiptProductUnitSerialArr['code'],
            'name' => $purchaseReceiptProductUnitSerialArr['name'],
        ]);
    }

    public function test_purchase_receipt_product_unit_serial_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->purchaseReceiptProductUnitSerialActions->create([]);
    }
}
