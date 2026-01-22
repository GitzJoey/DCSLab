<?php

namespace Tests\Unit\Actions\SaleReceiptProductUnitSerialActions;

use App\Actions\SaleReceiptProductUnitSerial\SaleReceiptProductUnitSerialActions;
use App\Models\Company;
use App\Models\SaleReceiptProductUnitSerial;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SaleReceiptProductUnitSerialActionsCreateTest extends ActionsTestCase
{
    private SaleReceiptProductUnitSerialActions $saleReceiptProductUnitSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleReceiptProductUnitSerialActions = new SaleReceiptProductUnitSerialActions();
    }

    public function test_sale_receipt_product_unit_serial_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $saleReceiptProductUnitSerialArr = SaleReceiptProductUnitSerial::factory()->for($company)
            ->make()->toArray();

        $result = $this->saleReceiptProductUnitSerialActions->create($saleReceiptProductUnitSerialArr);

        $this->assertDatabaseHas('sale_receipt_product_unit_serials', [
            'id' => $result->id,
            'company_id' => $saleReceiptProductUnitSerialArr['company_id'],
            'code' => $saleReceiptProductUnitSerialArr['code'],
            'name' => $saleReceiptProductUnitSerialArr['name'],
        ]);
    }

    public function test_sale_receipt_product_unit_serial_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->saleReceiptProductUnitSerialActions->create([]);
    }
}
