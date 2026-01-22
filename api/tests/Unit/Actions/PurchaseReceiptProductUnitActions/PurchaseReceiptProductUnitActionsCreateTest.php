<?php

namespace Tests\Unit\Actions\PurchaseReceiptProductUnitActions;

use App\Actions\PurchaseReceiptProductUnit\PurchaseReceiptProductUnitActions;
use App\Models\Company;
use App\Models\PurchaseReceiptProductUnit;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseReceiptProductUnitActionsCreateTest extends ActionsTestCase
{
    private PurchaseReceiptProductUnitActions $purchaseReceiptProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReceiptProductUnitActions = new PurchaseReceiptProductUnitActions();
    }

    public function test_purchase_receipt_product_unit_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $purchaseReceiptProductUnitArr = PurchaseReceiptProductUnit::factory()->for($company)
            ->make()->toArray();

        $result = $this->purchaseReceiptProductUnitActions->create($purchaseReceiptProductUnitArr);

        $this->assertDatabaseHas('purchase_receipt_product_units', [
            'id' => $result->id,
            'company_id' => $purchaseReceiptProductUnitArr['company_id'],
            'code' => $purchaseReceiptProductUnitArr['code'],
            'name' => $purchaseReceiptProductUnitArr['name'],
        ]);
    }

    public function test_purchase_receipt_product_unit_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->purchaseReceiptProductUnitActions->create([]);
    }
}
