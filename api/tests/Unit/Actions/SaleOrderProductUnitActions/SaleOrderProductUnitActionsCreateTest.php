<?php

namespace Tests\Unit\Actions\SaleOrderProductUnitActions;

use App\Actions\SaleOrderProductUnit\SaleOrderProductUnitActions;
use App\Models\Company;
use App\Models\SaleOrderProductUnit;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SaleOrderProductUnitActionsCreateTest extends ActionsTestCase
{
    private SaleOrderProductUnitActions $saleOrderProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleOrderProductUnitActions = new SaleOrderProductUnitActions();
    }

    public function test_sale_order_product_unit_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $saleOrderProductUnitArr = SaleOrderProductUnit::factory()->for($company)
            ->make()->toArray();

        $result = $this->saleOrderProductUnitActions->create($saleOrderProductUnitArr);

        $this->assertDatabaseHas('sale_order_product_units', [
            'id' => $result->id,
            'company_id' => $saleOrderProductUnitArr['company_id'],
            'code' => $saleOrderProductUnitArr['code'],
            'name' => $saleOrderProductUnitArr['name'],
        ]);
    }

    public function test_sale_order_product_unit_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->saleOrderProductUnitActions->create([]);
    }
}
