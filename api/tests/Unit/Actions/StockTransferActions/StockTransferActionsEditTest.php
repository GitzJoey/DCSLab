<?php

namespace Tests\Unit\Actions\StockTransferActions;

use App\Actions\StockTransfer\StockTransferActions;
use App\Models\Company;
use App\Models\StockTransfer;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class StockTransferActionsEditTest extends ActionsTestCase
{
    private StockTransferActions $stockTransferActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockTransferActions = new StockTransferActions();
    }

    public function test_stock_transfer_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransfer::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $stockTransfer = $company->stockTransfers()->inRandomOrder()->first();

        $stockTransferArr = StockTransfer::factory()->make()->toArray();

        $result = $this->stockTransferActions->update($stockTransfer, $stockTransferArr);

        $this->assertInstanceOf(StockTransfer::class, $result);
        $this->assertDatabaseHas('stock_transfers', [
            'id' => $stockTransfer->id,
            'company_id' => $stockTransfer->company_id,
            'code' => $stockTransferArr['code'],
            'name' => $stockTransferArr['name'],
        ]);
    }

    public function test_stock_transfer_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransfer::factory())
            )->create();

        $stockTransfer = $user->companies()->inRandomOrder()->first()
            ->stockTransfers()->inRandomOrder()->first();

        $stockTransferArr = [];

        $this->stockTransferActions->update($stockTransfer, $stockTransferArr);
    }
}
