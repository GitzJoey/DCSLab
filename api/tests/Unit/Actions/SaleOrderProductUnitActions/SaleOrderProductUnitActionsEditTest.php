<?php

namespace Tests\Unit\Actions\SaleOrderProductUnitActions;

use App\Actions\SaleOrderProductUnit\SaleOrderProductUnitActions;
use App\Models\Company;
use App\Models\SaleOrderProductUnit;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SaleOrderProductUnitActionsEditTest extends ActionsTestCase
{
    private SaleOrderProductUnitActions $saleOrderProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleOrderProductUnitActions = new SaleOrderProductUnitActions();
    }

    public function test_sale_order_product_unit_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleOrderProductUnit::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $saleOrderProductUnit = $company->saleOrderProductUnits()->inRandomOrder()->first();

        $saleOrderProductUnitArr = SaleOrderProductUnit::factory()->make()->toArray();

        $result = $this->saleOrderProductUnitActions->update($saleOrderProductUnit, $saleOrderProductUnitArr);

        $this->assertInstanceOf(SaleOrderProductUnit::class, $result);
        $this->assertDatabaseHas('sale_order_product_units', [
            'id' => $saleOrderProductUnit->id,
            'company_id' => $saleOrderProductUnit->company_id,
            'code' => $saleOrderProductUnitArr['code'],
            'name' => $saleOrderProductUnitArr['name'],
        ]);
    }

    public function test_sale_order_product_unit_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleOrderProductUnit::factory())
            )->create();

        $saleOrderProductUnit = $user->companies()->inRandomOrder()->first()
            ->saleOrderProductUnits()->inRandomOrder()->first();

        $saleOrderProductUnitArr = [];

        $this->saleOrderProductUnitActions->update($saleOrderProductUnit, $saleOrderProductUnitArr);
    }
}
