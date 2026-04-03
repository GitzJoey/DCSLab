<?php

namespace Tests\Unit\Actions\StockTransferItemActions;

use App\Actions\StockTransferItem\StockTransferItemActions;
use App\Models\Company;
use App\Models\StockTransferItem;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class StockTransferItemActionsCreateTest extends ActionsTestCase
{
    private StockTransferItemActions $stockTransferItemActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockTransferItemActions = new StockTransferItemActions();
    }

    public function test_stock_transfer_item_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $stockTransferItemArr = StockTransferItem::factory()->for($company)
            ->make()->toArray();

        $result = $this->stockTransferItemActions->create($stockTransferItemArr);

        $this->assertDatabaseHas('stock_transfer_items', [
            'id' => $result->id,
            'company_id' => $stockTransferItemArr['company_id'],
            'code' => $stockTransferItemArr['code'],
            'name' => $stockTransferItemArr['name'],
        ]);
    }

    public function test_stock_transfer_item_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->stockTransferItemActions->create([]);
    }
}
