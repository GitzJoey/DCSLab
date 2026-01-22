<?php

namespace Tests\Unit\Actions\SalesOrderActions;

use App\Actions\SalesOrder\SalesOrderActions;
use App\Models\Company;
use App\Models\SalesOrder;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SalesOrderActionsCreateTest extends ActionsTestCase
{
    private SalesOrderActions $salesOrderActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->salesOrderActions = new SalesOrderActions();
    }

    public function test_sales_order_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $salesOrderArr = SalesOrder::factory()->for($company)
            ->make()->toArray();

        $result = $this->salesOrderActions->create($salesOrderArr);

        $this->assertDatabaseHas('sales_orders', [
            'id' => $result->id,
            'company_id' => $salesOrderArr['company_id'],
            'code' => $salesOrderArr['code'],
            'name' => $salesOrderArr['name'],
        ]);
    }

    public function test_sales_order_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->salesOrderActions->create([]);
    }
}
