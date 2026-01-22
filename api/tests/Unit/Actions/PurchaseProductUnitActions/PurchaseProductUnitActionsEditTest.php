<?php

namespace Tests\Unit\Actions\PurchaseProductUnitActions;

use App\Actions\PurchaseProductUnit\PurchaseProductUnitActions;
use App\Models\Company;
use App\Models\PurchaseProductUnit;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseProductUnitActionsEditTest extends ActionsTestCase
{
    private PurchaseProductUnitActions $purchaseProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseProductUnitActions = new PurchaseProductUnitActions();
    }

    public function test_purchase_product_unit_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseProductUnit::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseProductUnit = $company->purchaseProductUnits()->inRandomOrder()->first();

        $purchaseProductUnitArr = PurchaseProductUnit::factory()->make()->toArray();

        $result = $this->purchaseProductUnitActions->update($purchaseProductUnit, $purchaseProductUnitArr);

        $this->assertInstanceOf(PurchaseProductUnit::class, $result);
        $this->assertDatabaseHas('purchase_product_units', [
            'id' => $purchaseProductUnit->id,
            'company_id' => $purchaseProductUnit->company_id,
            'code' => $purchaseProductUnitArr['code'],
            'name' => $purchaseProductUnitArr['name'],
        ]);
    }

    public function test_purchase_product_unit_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseProductUnit::factory())
            )->create();

        $purchaseProductUnit = $user->companies()->inRandomOrder()->first()
            ->purchaseProductUnits()->inRandomOrder()->first();

        $purchaseProductUnitArr = [];

        $this->purchaseProductUnitActions->update($purchaseProductUnit, $purchaseProductUnitArr);
    }
}
