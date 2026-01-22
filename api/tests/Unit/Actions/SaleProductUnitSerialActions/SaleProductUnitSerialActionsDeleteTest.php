<?php

namespace Tests\Unit\Actions\SaleProductUnitSerialActions;

use App\Actions\SaleProductUnitSerial\SaleProductUnitSerialActions;
use App\Models\Company;
use App\Models\SaleProductUnitSerial;
use App\Models\User;
use Tests\ActionsTestCase;

class SaleProductUnitSerialActionsDeleteTest extends ActionsTestCase
{
    private SaleProductUnitSerialActions $saleProductUnitSerialActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleProductUnitSerialActions = new SaleProductUnitSerialActions();
    }

    public function test_sale_product_unit_serial_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleProductUnitSerial::factory())
            )->create();

        $saleProductUnitSerial = $user->companies()->inRandomOrder()->first()
            ->saleProductUnitSerials()->inRandomOrder()->first();
        $result = $this->saleProductUnitSerialActions->delete($saleProductUnitSerial);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('sale_product_unit_serials', [
            'id' => $saleProductUnitSerial->id,
        ]);
    }
}
