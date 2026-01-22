<?php

namespace Tests\Unit\Actions\StockTransferActions;

use App\Actions\StockTransfer\StockTransferActions;
use App\Models\Company;
use App\Models\StockTransfer;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class StockTransferActionsCreateTest extends ActionsTestCase
{
    private StockTransferActions $stockTransferActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockTransferActions = new StockTransferActions();
    }

    public function test_stock_transfer_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $stockTransferArr = StockTransfer::factory()->for($company)
            ->make()->toArray();

        $result = $this->stockTransferActions->create($stockTransferArr);

        $this->assertDatabaseHas('stock_transfers', [
            'id' => $result->id,
            'company_id' => $stockTransferArr['company_id'],
            'code' => $stockTransferArr['code'],
            'name' => $stockTransferArr['name'],
        ]);
    }

    public function test_stock_transfer_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->stockTransferActions->create([]);
    }
}
