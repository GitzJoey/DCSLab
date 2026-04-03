<?php

namespace Tests\Unit\Actions\StockTransferItemSerialActions;

use App\Actions\StockTransferItemSerial\StockTransferItemSerialActions;
use App\Models\Company;
use App\Models\StockTransferItemSerial;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class StockTransferItemSerialActionsCreateTest extends ActionsTestCase
{
    private StockTransferItemSerialActions $stockTransferItemSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockTransferItemSerialActions = new StockTransferItemSerialActions();
    }

    public function test_stock_transfer_item_serial_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $stockTransferItemSerialArr = StockTransferItemSerial::factory()->for($company)
            ->make()->toArray();

        $result = $this->stockTransferItemSerialActions->create($stockTransferItemSerialArr);

        $this->assertDatabaseHas('stock_transfer_item_serials', [
            'id' => $result->id,
            'company_id' => $stockTransferItemSerialArr['company_id'],
            'code' => $stockTransferItemSerialArr['code'],
            'name' => $stockTransferItemSerialArr['name'],
        ]);
    }

    public function test_stock_transfer_item_serial_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->stockTransferItemSerialActions->create([]);
    }
}
