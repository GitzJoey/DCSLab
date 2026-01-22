<?php

namespace Tests\Unit\Actions\SaleReceiptProductUnitActions;

use App\Actions\SaleReceiptProductUnit\SaleReceiptProductUnitActions;
use App\Models\Company;
use App\Models\SaleReceiptProductUnit;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SaleReceiptProductUnitActionsCreateTest extends ActionsTestCase
{
    private SaleReceiptProductUnitActions $saleReceiptProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleReceiptProductUnitActions = new SaleReceiptProductUnitActions();
    }

    public function test_sale_receipt_product_unit_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $saleReceiptProductUnitArr = SaleReceiptProductUnit::factory()->for($company)
            ->make()->toArray();

        $result = $this->saleReceiptProductUnitActions->create($saleReceiptProductUnitArr);

        $this->assertDatabaseHas('sale_receipt_product_units', [
            'id' => $result->id,
            'company_id' => $saleReceiptProductUnitArr['company_id'],
            'code' => $saleReceiptProductUnitArr['code'],
            'name' => $saleReceiptProductUnitArr['name'],
        ]);
    }

    public function test_sale_receipt_product_unit_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->saleReceiptProductUnitActions->create([]);
    }
}
