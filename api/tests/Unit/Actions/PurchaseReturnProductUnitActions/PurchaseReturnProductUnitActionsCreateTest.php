<?php

namespace Tests\Unit\Actions\PurchaseReturnProductUnitActions;

use App\Actions\PurchaseReturnProductUnit\PurchaseReturnProductUnitActions;
use App\Models\Company;
use App\Models\PurchaseReturnProductUnit;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseReturnProductUnitActionsCreateTest extends ActionsTestCase
{
    private PurchaseReturnProductUnitActions $purchaseReturnProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReturnProductUnitActions = new PurchaseReturnProductUnitActions();
    }

    public function test_purchase_return_product_unit_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $purchaseReturnProductUnitArr = PurchaseReturnProductUnit::factory()->for($company)
            ->make()->toArray();

        $result = $this->purchaseReturnProductUnitActions->create($purchaseReturnProductUnitArr);

        $this->assertDatabaseHas('purchase_return_product_units', [
            'id' => $result->id,
            'company_id' => $purchaseReturnProductUnitArr['company_id'],
            'code' => $purchaseReturnProductUnitArr['code'],
            'name' => $purchaseReturnProductUnitArr['name'],
        ]);
    }

    public function test_purchase_return_product_unit_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->purchaseReturnProductUnitActions->create([]);
    }
}
