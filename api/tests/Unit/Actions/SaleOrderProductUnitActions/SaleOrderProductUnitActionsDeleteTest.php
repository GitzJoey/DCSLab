<?php

namespace Tests\Unit\Actions\SaleOrderProductUnitActions;

use App\Actions\SaleOrderProductUnit\SaleOrderProductUnitActions;
use App\Models\Company;
use App\Models\SaleOrderProductUnit;
use App\Models\User;
use Tests\ActionsTestCase;

class SaleOrderProductUnitActionsDeleteTest extends ActionsTestCase
{
    private SaleOrderProductUnitActions $saleOrderProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleOrderProductUnitActions = new SaleOrderProductUnitActions();
    }

    public function test_sale_order_product_unit_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleOrderProductUnit::factory())
            )->create();

        $saleOrderProductUnit = $user->companies()->inRandomOrder()->first()
            ->saleOrderProductUnits()->inRandomOrder()->first();
        $result = $this->saleOrderProductUnitActions->delete($saleOrderProductUnit);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('sale_order_product_units', [
            'id' => $saleOrderProductUnit->id,
        ]);
    }
}
