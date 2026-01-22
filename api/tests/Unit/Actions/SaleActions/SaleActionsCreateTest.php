<?php

namespace Tests\Unit\Actions\SaleActions;

use App\Actions\Sale\SaleActions;
use App\Models\Company;
use App\Models\Sale;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SaleActionsCreateTest extends ActionsTestCase
{
    private SaleActions $saleActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleActions = new SaleActions();
    }

    public function test_sale_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $saleArr = Sale::factory()->for($company)
            ->make()->toArray();

        $result = $this->saleActions->create($saleArr);

        $this->assertDatabaseHas('sales', [
            'id' => $result->id,
            'company_id' => $saleArr['company_id'],
            'code' => $saleArr['code'],
            'name' => $saleArr['name'],
        ]);
    }

    public function test_sale_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->saleActions->create([]);
    }
}
