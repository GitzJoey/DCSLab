<?php

namespace Tests\Unit\Actions\StockTransferProductUnitSerialActions;

use App\Actions\StockTransferProductUnitSerial\StockTransferProductUnitSerialActions;
use App\Models\Company;
use App\Models\StockTransferProductUnitSerial;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class StockTransferProductUnitSerialActionsCreateTest extends ActionsTestCase
{
    private StockTransferProductUnitSerialActions $stockTransferProductUnitSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockTransferProductUnitSerialActions = new StockTransferProductUnitSerialActions();
    }

    public function test_stock_transfer_product_unit_serial_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $stockTransferProductUnitSerialArr = StockTransferProductUnitSerial::factory()->for($company)
            ->make()->toArray();

        $result = $this->stockTransferProductUnitSerialActions->create($stockTransferProductUnitSerialArr);

        $this->assertDatabaseHas('stock_transfer_product_unit_serials', [
            'id' => $result->id,
            'company_id' => $stockTransferProductUnitSerialArr['company_id'],
            'code' => $stockTransferProductUnitSerialArr['code'],
            'name' => $stockTransferProductUnitSerialArr['name'],
        ]);
    }

    public function test_stock_transfer_product_unit_serial_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->stockTransferProductUnitSerialActions->create([]);
    }
}
