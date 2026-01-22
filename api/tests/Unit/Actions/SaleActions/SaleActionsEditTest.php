<?php

namespace Tests\Unit\Actions\SaleActions;

use App\Actions\Sale\SaleActions;
use App\Models\Company;
use App\Models\Sale;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SaleActionsEditTest extends ActionsTestCase
{
    private SaleActions $saleActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleActions = new SaleActions();
    }

    public function test_sale_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Sale::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $sale = $company->sales()->inRandomOrder()->first();

        $saleArr = Sale::factory()->make()->toArray();

        $result = $this->saleActions->update($sale, $saleArr);

        $this->assertInstanceOf(Sale::class, $result);
        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'company_id' => $sale->company_id,
            'code' => $saleArr['code'],
            'name' => $saleArr['name'],
        ]);
    }

    public function test_sale_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Sale::factory())
            )->create();

        $sale = $user->companies()->inRandomOrder()->first()
            ->sales()->inRandomOrder()->first();

        $saleArr = [];

        $this->saleActions->update($sale, $saleArr);
    }
}
