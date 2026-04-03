<?php

namespace Tests\Unit\Actions\StockTransferItemSerialActions;

use App\Actions\StockTransferItemSerial\StockTransferItemSerialActions;
use App\Models\Company;
use App\Models\StockTransferItemSerial;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class StockTransferItemSerialActionsEditTest extends ActionsTestCase
{
    private StockTransferItemSerialActions $stockTransferItemSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockTransferItemSerialActions = new StockTransferItemSerialActions();
    }

    public function test_stock_transfer_item_serial_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransferItemSerial::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $stockTransferItemSerial = $company->stockTransferItemSerials()->inRandomOrder()->first();

        $stockTransferItemSerialArr = StockTransferItemSerial::factory()->make()->toArray();

        $result = $this->stockTransferItemSerialActions->update($stockTransferItemSerial, $stockTransferItemSerialArr);

        $this->assertInstanceOf(StockTransferItemSerial::class, $result);
        $this->assertDatabaseHas('stock_transfer_item_serials', [
            'id' => $stockTransferItemSerial->id,
            'company_id' => $stockTransferItemSerial->company_id,
            'code' => $stockTransferItemSerialArr['code'],
            'name' => $stockTransferItemSerialArr['name'],
        ]);
    }

    public function test_stock_transfer_item_serial_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransferItemSerial::factory())
            )->create();

        $stockTransferItemSerial = $user->companies()->inRandomOrder()->first()
            ->stockTransferItemSerials()->inRandomOrder()->first();

        $stockTransferItemSerialArr = [];

        $this->stockTransferItemSerialActions->update($stockTransferItemSerial, $stockTransferItemSerialArr);
    }
}
