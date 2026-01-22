<?php

namespace Tests\Unit\Actions\StockTransferProductUnitSerialActions;

use App\Actions\StockTransferProductUnitSerial\StockTransferProductUnitSerialActions;
use App\Models\Company;
use App\Models\StockTransferProductUnitSerial;
use App\Models\User;
use Tests\ActionsTestCase;

class StockTransferProductUnitSerialActionsDeleteTest extends ActionsTestCase
{
    private StockTransferProductUnitSerialActions $stockTransferProductUnitSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockTransferProductUnitSerialActions = new StockTransferProductUnitSerialActions();
    }

    public function test_stock_transfer_product_unit_serial_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransferProductUnitSerial::factory())
            )->create();

        $stockTransferProductUnitSerial = $user->companies()->inRandomOrder()->first()
            ->stockTransferProductUnitSerials()->inRandomOrder()->first();
        $result = $this->stockTransferProductUnitSerialActions->delete($stockTransferProductUnitSerial);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('stock_transfer_product_unit_serials', [
            'id' => $stockTransferProductUnitSerial->id,
        ]);
    }
}
