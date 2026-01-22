<?php

namespace Tests\Unit\Actions\StockTransferProductUnitActions;

use App\Actions\StockTransferProductUnit\StockTransferProductUnitActions;
use App\Models\Company;
use App\Models\StockTransferProductUnit;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class StockTransferProductUnitActionsEditTest extends ActionsTestCase
{
    private StockTransferProductUnitActions $stockTransferProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockTransferProductUnitActions = new StockTransferProductUnitActions();
    }

    public function test_stock_transfer_product_unit_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransferProductUnit::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $stockTransferProductUnit = $company->stockTransferProductUnits()->inRandomOrder()->first();

        $stockTransferProductUnitArr = StockTransferProductUnit::factory()->make()->toArray();

        $result = $this->stockTransferProductUnitActions->update($stockTransferProductUnit, $stockTransferProductUnitArr);

        $this->assertInstanceOf(StockTransferProductUnit::class, $result);
        $this->assertDatabaseHas('stock_transfer_product_units', [
            'id' => $stockTransferProductUnit->id,
            'company_id' => $stockTransferProductUnit->company_id,
            'code' => $stockTransferProductUnitArr['code'],
            'name' => $stockTransferProductUnitArr['name'],
        ]);
    }

    public function test_stock_transfer_product_unit_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransferProductUnit::factory())
            )->create();

        $stockTransferProductUnit = $user->companies()->inRandomOrder()->first()
            ->stockTransferProductUnits()->inRandomOrder()->first();

        $stockTransferProductUnitArr = [];

        $this->stockTransferProductUnitActions->update($stockTransferProductUnit, $stockTransferProductUnitArr);
    }
}
