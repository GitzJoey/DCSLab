<?php

namespace Tests\Unit\Actions\StockTransferProductUnitSerialActions;

use App\Actions\StockTransferProductUnitSerial\StockTransferProductUnitSerialActions;
use App\Models\Company;
use App\Models\StockTransferProductUnitSerial;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class StockTransferProductUnitSerialActionsEditTest extends ActionsTestCase
{
    private StockTransferProductUnitSerialActions $stockTransferProductUnitSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockTransferProductUnitSerialActions = new StockTransferProductUnitSerialActions();
    }

    public function test_stock_transfer_product_unit_serial_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransferProductUnitSerial::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $stockTransferProductUnitSerial = $company->stockTransferProductUnitSerials()->inRandomOrder()->first();

        $stockTransferProductUnitSerialArr = StockTransferProductUnitSerial::factory()->make()->toArray();

        $result = $this->stockTransferProductUnitSerialActions->update($stockTransferProductUnitSerial, $stockTransferProductUnitSerialArr);

        $this->assertInstanceOf(StockTransferProductUnitSerial::class, $result);
        $this->assertDatabaseHas('stock_transfer_product_unit_serials', [
            'id' => $stockTransferProductUnitSerial->id,
            'company_id' => $stockTransferProductUnitSerial->company_id,
            'code' => $stockTransferProductUnitSerialArr['code'],
            'name' => $stockTransferProductUnitSerialArr['name'],
        ]);
    }

    public function test_stock_transfer_product_unit_serial_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransferProductUnitSerial::factory())
            )->create();

        $stockTransferProductUnitSerial = $user->companies()->inRandomOrder()->first()
            ->stockTransferProductUnitSerials()->inRandomOrder()->first();

        $stockTransferProductUnitSerialArr = [];

        $this->stockTransferProductUnitSerialActions->update($stockTransferProductUnitSerial, $stockTransferProductUnitSerialArr);
    }
}
