<?php

namespace Tests\Unit\Actions\PurchaseOrderProductUnitActions;

use App\Actions\PurchaseOrderProductUnit\PurchaseOrderProductUnitActions;
use App\Models\Company;
use App\Models\PurchaseOrderProductUnit;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseOrderProductUnitActionsCreateTest extends ActionsTestCase
{
    private PurchaseOrderProductUnitActions $purchaseOrderProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseOrderProductUnitActions = new PurchaseOrderProductUnitActions();
    }

    public function test_purchase_order_product_unit_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $purchaseOrderProductUnitArr = PurchaseOrderProductUnit::factory()->for($company)
            ->make()->toArray();

        $result = $this->purchaseOrderProductUnitActions->create($purchaseOrderProductUnitArr);

        $this->assertDatabaseHas('purchase_order_product_units', [
            'id' => $result->id,
            'company_id' => $purchaseOrderProductUnitArr['company_id'],
            'code' => $purchaseOrderProductUnitArr['code'],
            'name' => $purchaseOrderProductUnitArr['name'],
        ]);
    }

    public function test_purchase_order_product_unit_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->purchaseOrderProductUnitActions->create([]);
    }
}
