<?php

namespace Tests\Unit\Actions\PurchaseProductUnitActions;

use App\Actions\PurchaseProductUnit\PurchaseProductUnitActions;
use App\Models\Company;
use App\Models\PurchaseProductUnit;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseProductUnitActionsCreateTest extends ActionsTestCase
{
    private PurchaseProductUnitActions $purchaseProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseProductUnitActions = new PurchaseProductUnitActions();
    }

    public function test_purchase_product_unit_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $purchaseProductUnitArr = PurchaseProductUnit::factory()->for($company)
            ->make()->toArray();

        $result = $this->purchaseProductUnitActions->create($purchaseProductUnitArr);

        $this->assertDatabaseHas('purchase_product_units', [
            'id' => $result->id,
            'company_id' => $purchaseProductUnitArr['company_id'],
            'code' => $purchaseProductUnitArr['code'],
            'name' => $purchaseProductUnitArr['name'],
        ]);
    }

    public function test_purchase_product_unit_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->purchaseProductUnitActions->create([]);
    }
}
