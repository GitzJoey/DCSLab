<?php

namespace Tests\Unit\Actions\SaleReceiptProductUnitActions;

use App\Actions\SaleReceiptProductUnit\SaleReceiptProductUnitActions;
use App\Models\Company;
use App\Models\SaleReceiptProductUnit;
use App\Models\User;
use Tests\ActionsTestCase;

class SaleReceiptProductUnitActionsDeleteTest extends ActionsTestCase
{
    private SaleReceiptProductUnitActions $saleReceiptProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleReceiptProductUnitActions = new SaleReceiptProductUnitActions();
    }

    public function test_sale_receipt_product_unit_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleReceiptProductUnit::factory())
            )->create();

        $saleReceiptProductUnit = $user->companies()->inRandomOrder()->first()
            ->saleReceiptProductUnits()->inRandomOrder()->first();
        $result = $this->saleReceiptProductUnitActions->delete($saleReceiptProductUnit);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('sale_receipt_product_units', [
            'id' => $saleReceiptProductUnit->id,
        ]);
    }
}
