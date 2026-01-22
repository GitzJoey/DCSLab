<?php

namespace Tests\Unit\Actions\SaleProductUnitActions;

use App\Actions\SaleProductUnit\SaleProductUnitActions;
use App\Models\Company;
use App\Models\SaleProductUnit;
use App\Models\User;
use Tests\ActionsTestCase;

class SaleProductUnitActionsDeleteTest extends ActionsTestCase
{
    private SaleProductUnitActions $saleProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleProductUnitActions = new SaleProductUnitActions();
    }

    public function test_sale_product_unit_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleProductUnit::factory())
            )->create();

        $saleProductUnit = $user->companies()->inRandomOrder()->first()
            ->saleProductUnits()->inRandomOrder()->first();
        $result = $this->saleProductUnitActions->delete($saleProductUnit);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('sale_product_units', [
            'id' => $saleProductUnit->id,
        ]);
    }
}
