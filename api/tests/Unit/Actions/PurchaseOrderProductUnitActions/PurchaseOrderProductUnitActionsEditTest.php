<?php

namespace Tests\Unit\Actions\PurchaseOrderProductUnitActions;

use App\Actions\PurchaseOrderProductUnit\PurchaseOrderProductUnitActions;
use App\Models\Company;
use App\Models\PurchaseOrderProductUnit;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseOrderProductUnitActionsEditTest extends ActionsTestCase
{
    private PurchaseOrderProductUnitActions $purchaseOrderProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseOrderProductUnitActions = new PurchaseOrderProductUnitActions();
    }

    public function test_purchase_order_product_unit_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseOrderProductUnit::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseOrderProductUnit = $company->purchaseOrderProductUnits()->inRandomOrder()->first();

        $purchaseOrderProductUnitArr = PurchaseOrderProductUnit::factory()->make()->toArray();

        $result = $this->purchaseOrderProductUnitActions->update($purchaseOrderProductUnit, $purchaseOrderProductUnitArr);

        $this->assertInstanceOf(PurchaseOrderProductUnit::class, $result);
        $this->assertDatabaseHas('purchase_order_product_units', [
            'id' => $purchaseOrderProductUnit->id,
            'company_id' => $purchaseOrderProductUnit->company_id,
            'code' => $purchaseOrderProductUnitArr['code'],
            'name' => $purchaseOrderProductUnitArr['name'],
        ]);
    }

    public function test_purchase_order_product_unit_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseOrderProductUnit::factory())
            )->create();

        $purchaseOrderProductUnit = $user->companies()->inRandomOrder()->first()
            ->purchaseOrderProductUnits()->inRandomOrder()->first();

        $purchaseOrderProductUnitArr = [];

        $this->purchaseOrderProductUnitActions->update($purchaseOrderProductUnit, $purchaseOrderProductUnitArr);
    }
}
