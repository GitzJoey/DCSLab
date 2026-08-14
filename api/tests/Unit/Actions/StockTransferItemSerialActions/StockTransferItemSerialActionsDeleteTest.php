<?php

namespace Tests\Unit\Actions\StockTransferItemSerialActions;

use App\Actions\StockTransferItemSerial\StockTransferItemSerialActions;
use App\Models\Company;
use App\Models\StockTransferItemSerial;
use App\Models\User;
use Tests\ActionsTestCase;

class StockTransferItemSerialActionsDeleteTest extends ActionsTestCase
{
    private StockTransferItemSerialActions $stockTransferItemSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockTransferItemSerialActions = new StockTransferItemSerialActions();
    }

    public function test_stock_transfer_item_serial_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransferItemSerial::factory())
            )->create();

        $stockTransferItemSerial = $user->companies()->inRandomOrder()->first()
            ->stockTransferItemSerials()->inRandomOrder()->first();
        $result = $this->stockTransferItemSerialActions->delete($stockTransferItemSerial);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('stock_transfer_item_serials', [
            'id' => $stockTransferItemSerial->id,
        ]);
    }
}
