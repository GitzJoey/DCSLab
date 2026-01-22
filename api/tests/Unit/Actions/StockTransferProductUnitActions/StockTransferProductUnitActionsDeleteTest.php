<?php

namespace Tests\Unit\Actions\StockTransferProductUnitActions;

use App\Actions\StockTransferProductUnit\StockTransferProductUnitActions;
use App\Models\Company;
use App\Models\StockTransferProductUnit;
use App\Models\User;
use Tests\ActionsTestCase;

class StockTransferProductUnitActionsDeleteTest extends ActionsTestCase
{
    private StockTransferProductUnitActions $stockTransferProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockTransferProductUnitActions = new StockTransferProductUnitActions();
    }

    public function test_stock_transfer_product_unit_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransferProductUnit::factory())
            )->create();

        $stockTransferProductUnit = $user->companies()->inRandomOrder()->first()
            ->stockTransferProductUnits()->inRandomOrder()->first();
        $result = $this->stockTransferProductUnitActions->delete($stockTransferProductUnit);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('stock_transfer_product_units', [
            'id' => $stockTransferProductUnit->id,
        ]);
    }
}
