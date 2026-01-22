<?php

namespace Tests\Unit\Actions\SaleReceiptProductUnitSerialActions;

use App\Actions\SaleReceiptProductUnitSerial\SaleReceiptProductUnitSerialActions;
use App\Models\Company;
use App\Models\SaleReceiptProductUnitSerial;
use App\Models\User;
use Tests\ActionsTestCase;

class SaleReceiptProductUnitSerialActionsDeleteTest extends ActionsTestCase
{
    private SaleReceiptProductUnitSerialActions $saleReceiptProductUnitSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleReceiptProductUnitSerialActions = new SaleReceiptProductUnitSerialActions();
    }

    public function test_sale_receipt_product_unit_serial_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleReceiptProductUnitSerial::factory())
            )->create();

        $saleReceiptProductUnitSerial = $user->companies()->inRandomOrder()->first()
            ->saleReceiptProductUnitSerials()->inRandomOrder()->first();
        $result = $this->saleReceiptProductUnitSerialActions->delete($saleReceiptProductUnitSerial);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('sale_receipt_product_unit_serials', [
            'id' => $saleReceiptProductUnitSerial->id,
        ]);
    }
}
