<?php

namespace Tests\Unit\Actions\StockTransferActions;

use App\Actions\StockTransfer\StockTransferActions;
use App\Models\Company;
use App\Models\StockTransfer;
use App\Models\User;
use Tests\ActionsTestCase;

class StockTransferActionsDeleteTest extends ActionsTestCase
{
    private StockTransferActions $stockTransferActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockTransferActions = new StockTransferActions();
    }

    public function test_stock_transfer_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransfer::factory())
            )->create();

        $stockTransfer = $user->companies()->inRandomOrder()->first()
            ->stockTransfers()->inRandomOrder()->first();
        $result = $this->stockTransferActions->delete($stockTransfer);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('stock_transfers', [
            'id' => $stockTransfer->id,
        ]);
    }
}
