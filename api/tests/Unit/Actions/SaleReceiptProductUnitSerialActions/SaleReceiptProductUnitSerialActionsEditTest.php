<?php

namespace Tests\Unit\Actions\SaleReceiptProductUnitSerialActions;

use App\Actions\SaleReceiptProductUnitSerial\SaleReceiptProductUnitSerialActions;
use App\Models\Company;
use App\Models\SaleReceiptProductUnitSerial;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SaleReceiptProductUnitSerialActionsEditTest extends ActionsTestCase
{
    private SaleReceiptProductUnitSerialActions $saleReceiptProductUnitSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleReceiptProductUnitSerialActions = new SaleReceiptProductUnitSerialActions();
    }

    public function test_sale_receipt_product_unit_serial_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleReceiptProductUnitSerial::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $saleReceiptProductUnitSerial = $company->saleReceiptProductUnitSerials()->inRandomOrder()->first();

        $saleReceiptProductUnitSerialArr = SaleReceiptProductUnitSerial::factory()->make()->toArray();

        $result = $this->saleReceiptProductUnitSerialActions->update($saleReceiptProductUnitSerial, $saleReceiptProductUnitSerialArr);

        $this->assertInstanceOf(SaleReceiptProductUnitSerial::class, $result);
        $this->assertDatabaseHas('sale_receipt_product_unit_serials', [
            'id' => $saleReceiptProductUnitSerial->id,
            'company_id' => $saleReceiptProductUnitSerial->company_id,
            'code' => $saleReceiptProductUnitSerialArr['code'],
            'name' => $saleReceiptProductUnitSerialArr['name'],
        ]);
    }

    public function test_sale_receipt_product_unit_serial_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleReceiptProductUnitSerial::factory())
            )->create();

        $saleReceiptProductUnitSerial = $user->companies()->inRandomOrder()->first()
            ->saleReceiptProductUnitSerials()->inRandomOrder()->first();

        $saleReceiptProductUnitSerialArr = [];

        $this->saleReceiptProductUnitSerialActions->update($saleReceiptProductUnitSerial, $saleReceiptProductUnitSerialArr);
    }
}
