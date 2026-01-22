<?php

namespace Tests\Unit\Actions\PurchaseReceiptProductUnitActions;

use App\Actions\PurchaseReceiptProductUnit\PurchaseReceiptProductUnitActions;
use App\Models\Company;
use App\Models\PurchaseReceiptProductUnit;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseReceiptProductUnitActionsEditTest extends ActionsTestCase
{
    private PurchaseReceiptProductUnitActions $purchaseReceiptProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReceiptProductUnitActions = new PurchaseReceiptProductUnitActions();
    }

    public function test_purchase_receipt_product_unit_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReceiptProductUnit::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseReceiptProductUnit = $company->purchaseReceiptProductUnits()->inRandomOrder()->first();

        $purchaseReceiptProductUnitArr = PurchaseReceiptProductUnit::factory()->make()->toArray();

        $result = $this->purchaseReceiptProductUnitActions->update($purchaseReceiptProductUnit, $purchaseReceiptProductUnitArr);

        $this->assertInstanceOf(PurchaseReceiptProductUnit::class, $result);
        $this->assertDatabaseHas('purchase_receipt_product_units', [
            'id' => $purchaseReceiptProductUnit->id,
            'company_id' => $purchaseReceiptProductUnit->company_id,
            'code' => $purchaseReceiptProductUnitArr['code'],
            'name' => $purchaseReceiptProductUnitArr['name'],
        ]);
    }

    public function test_purchase_receipt_product_unit_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReceiptProductUnit::factory())
            )->create();

        $purchaseReceiptProductUnit = $user->companies()->inRandomOrder()->first()
            ->purchaseReceiptProductUnits()->inRandomOrder()->first();

        $purchaseReceiptProductUnitArr = [];

        $this->purchaseReceiptProductUnitActions->update($purchaseReceiptProductUnit, $purchaseReceiptProductUnitArr);
    }
}
