<?php

namespace Tests\Unit\Actions\SaleReceiptProductUnitActions;

use App\Actions\SaleReceiptProductUnit\SaleReceiptProductUnitActions;
use App\Models\Company;
use App\Models\SaleReceiptProductUnit;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SaleReceiptProductUnitActionsEditTest extends ActionsTestCase
{
    private SaleReceiptProductUnitActions $saleReceiptProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleReceiptProductUnitActions = new SaleReceiptProductUnitActions();
    }

    public function test_sale_receipt_product_unit_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleReceiptProductUnit::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $saleReceiptProductUnit = $company->saleReceiptProductUnits()->inRandomOrder()->first();

        $saleReceiptProductUnitArr = SaleReceiptProductUnit::factory()->make()->toArray();

        $result = $this->saleReceiptProductUnitActions->update($saleReceiptProductUnit, $saleReceiptProductUnitArr);

        $this->assertInstanceOf(SaleReceiptProductUnit::class, $result);
        $this->assertDatabaseHas('sale_receipt_product_units', [
            'id' => $saleReceiptProductUnit->id,
            'company_id' => $saleReceiptProductUnit->company_id,
            'code' => $saleReceiptProductUnitArr['code'],
            'name' => $saleReceiptProductUnitArr['name'],
        ]);
    }

    public function test_sale_receipt_product_unit_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleReceiptProductUnit::factory())
            )->create();

        $saleReceiptProductUnit = $user->companies()->inRandomOrder()->first()
            ->saleReceiptProductUnits()->inRandomOrder()->first();

        $saleReceiptProductUnitArr = [];

        $this->saleReceiptProductUnitActions->update($saleReceiptProductUnit, $saleReceiptProductUnitArr);
    }
}
