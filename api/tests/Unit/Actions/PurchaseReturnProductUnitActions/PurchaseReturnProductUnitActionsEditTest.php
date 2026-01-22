<?php

namespace Tests\Unit\Actions\PurchaseReturnProductUnitActions;

use App\Actions\PurchaseReturnProductUnit\PurchaseReturnProductUnitActions;
use App\Models\Company;
use App\Models\PurchaseReturnProductUnit;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseReturnProductUnitActionsEditTest extends ActionsTestCase
{
    private PurchaseReturnProductUnitActions $purchaseReturnProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReturnProductUnitActions = new PurchaseReturnProductUnitActions();
    }

    public function test_purchase_return_product_unit_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReturnProductUnit::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseReturnProductUnit = $company->purchaseReturnProductUnits()->inRandomOrder()->first();

        $purchaseReturnProductUnitArr = PurchaseReturnProductUnit::factory()->make()->toArray();

        $result = $this->purchaseReturnProductUnitActions->update($purchaseReturnProductUnit, $purchaseReturnProductUnitArr);

        $this->assertInstanceOf(PurchaseReturnProductUnit::class, $result);
        $this->assertDatabaseHas('purchase_return_product_units', [
            'id' => $purchaseReturnProductUnit->id,
            'company_id' => $purchaseReturnProductUnit->company_id,
            'code' => $purchaseReturnProductUnitArr['code'],
            'name' => $purchaseReturnProductUnitArr['name'],
        ]);
    }

    public function test_purchase_return_product_unit_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReturnProductUnit::factory())
            )->create();

        $purchaseReturnProductUnit = $user->companies()->inRandomOrder()->first()
            ->purchaseReturnProductUnits()->inRandomOrder()->first();

        $purchaseReturnProductUnitArr = [];

        $this->purchaseReturnProductUnitActions->update($purchaseReturnProductUnit, $purchaseReturnProductUnitArr);
    }
}
