<?php

namespace Tests\Unit\Actions\SaleProductUnitActions;

use App\Actions\SaleProductUnit\SaleProductUnitActions;
use App\Models\Company;
use App\Models\SaleProductUnit;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SaleProductUnitActionsEditTest extends ActionsTestCase
{
    private SaleProductUnitActions $saleProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleProductUnitActions = new SaleProductUnitActions();
    }

    public function test_sale_product_unit_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleProductUnit::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $saleProductUnit = $company->saleProductUnits()->inRandomOrder()->first();

        $saleProductUnitArr = SaleProductUnit::factory()->make()->toArray();

        $result = $this->saleProductUnitActions->update($saleProductUnit, $saleProductUnitArr);

        $this->assertInstanceOf(SaleProductUnit::class, $result);
        $this->assertDatabaseHas('sale_product_units', [
            'id' => $saleProductUnit->id,
            'company_id' => $saleProductUnit->company_id,
            'code' => $saleProductUnitArr['code'],
            'name' => $saleProductUnitArr['name'],
        ]);
    }

    public function test_sale_product_unit_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleProductUnit::factory())
            )->create();

        $saleProductUnit = $user->companies()->inRandomOrder()->first()
            ->saleProductUnits()->inRandomOrder()->first();

        $saleProductUnitArr = [];

        $this->saleProductUnitActions->update($saleProductUnit, $saleProductUnitArr);
    }
}
