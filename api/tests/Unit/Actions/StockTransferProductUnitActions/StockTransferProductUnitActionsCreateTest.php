<?php

namespace Tests\Unit\Actions\StockTransferProductUnitActions;

use App\Actions\StockTransferProductUnit\StockTransferProductUnitActions;
use App\Models\Company;
use App\Models\StockTransferProductUnit;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class StockTransferProductUnitActionsCreateTest extends ActionsTestCase
{
    private StockTransferProductUnitActions $stockTransferProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockTransferProductUnitActions = new StockTransferProductUnitActions();
    }

    public function test_stock_transfer_product_unit_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $stockTransferProductUnitArr = StockTransferProductUnit::factory()->for($company)
            ->make()->toArray();

        $result = $this->stockTransferProductUnitActions->create($stockTransferProductUnitArr);

        $this->assertDatabaseHas('stock_transfer_product_units', [
            'id' => $result->id,
            'company_id' => $stockTransferProductUnitArr['company_id'],
            'code' => $stockTransferProductUnitArr['code'],
            'name' => $stockTransferProductUnitArr['name'],
        ]);
    }

    public function test_stock_transfer_product_unit_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->stockTransferProductUnitActions->create([]);
    }
}
