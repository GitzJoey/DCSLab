<?php

namespace Tests\Unit\Actions\StockTransferItemActions;

use App\Actions\StockTransferItem\StockTransferItemActions;
use App\Models\Company;
use App\Models\StockTransferItem;
use App\Models\User;
use Tests\ActionsTestCase;

class StockTransferItemActionsDeleteTest extends ActionsTestCase
{
    private StockTransferItemActions $stockTransferItemActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockTransferItemActions = new StockTransferItemActions();
    }

    public function test_stock_transfer_item_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransferItem::factory())
            )->create();

        $stockTransferItem = $user->companies()->inRandomOrder()->first()
            ->stockTransferItems()->inRandomOrder()->first();
        $result = $this->stockTransferItemActions->delete($stockTransferItem);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('stock_transfer_items', [
            'id' => $stockTransferItem->id,
        ]);
    }
}
