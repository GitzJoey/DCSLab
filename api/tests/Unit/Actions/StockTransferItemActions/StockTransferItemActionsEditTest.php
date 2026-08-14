<?php

namespace Tests\Unit\Actions\StockTransferItemActions;

use App\Actions\StockTransferItem\StockTransferItemActions;
use App\Models\Company;
use App\Models\StockTransferItem;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class StockTransferItemActionsEditTest extends ActionsTestCase
{
    private StockTransferItemActions $stockTransferItemActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockTransferItemActions = new StockTransferItemActions();
    }

    public function test_stock_transfer_item_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransferItem::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $stockTransferItem = $company->stockTransferItems()->inRandomOrder()->first();

        $payload = StockTransferItem::factory()->make()->toArray();

        $result = $this->stockTransferItemActions->update($stockTransferItem, $payload);

        $this->assertInstanceOf(StockTransferItem::class, $result);
        $this->assertDatabaseHas('stock_transfer_items', [
            'id' => $stockTransferItem->id,
            'company_id' => $stockTransferItem->company_id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_stock_transfer_item_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransferItem::factory())
            )->create();

        $stockTransferItem = $user->companies()->inRandomOrder()->first()
            ->stockTransferItems()->inRandomOrder()->first();

        $payload = [];

        $this->stockTransferItemActions->update($stockTransferItem, $payload);
    }
}
