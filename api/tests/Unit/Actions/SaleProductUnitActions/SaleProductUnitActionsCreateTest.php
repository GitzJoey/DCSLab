<?php

namespace Tests\Unit\Actions\SaleProductUnitActions;

use App\Actions\SaleProductUnit\SaleProductUnitActions;
use App\Models\Company;
use App\Models\SaleProductUnit;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SaleProductUnitActionsCreateTest extends ActionsTestCase
{
    private SaleProductUnitActions $saleProductUnitActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleProductUnitActions = new SaleProductUnitActions();
    }

    public function test_sale_product_unit_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $saleProductUnitArr = SaleProductUnit::factory()->for($company)
            ->make()->toArray();

        $result = $this->saleProductUnitActions->create($saleProductUnitArr);

        $this->assertDatabaseHas('sale_product_units', [
            'id' => $result->id,
            'company_id' => $saleProductUnitArr['company_id'],
            'code' => $saleProductUnitArr['code'],
            'name' => $saleProductUnitArr['name'],
        ]);
    }

    public function test_sale_product_unit_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->saleProductUnitActions->create([]);
    }
}
