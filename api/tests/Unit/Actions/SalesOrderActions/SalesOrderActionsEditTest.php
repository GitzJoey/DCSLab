<?php

namespace Tests\Unit\Actions\SalesOrderActions;

use App\Actions\SalesOrder\SalesOrderActions;
use App\Models\Company;
use App\Models\SalesOrder;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SalesOrderActionsEditTest extends ActionsTestCase
{
    private SalesOrderActions $salesOrderActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->salesOrderActions = new SalesOrderActions();
    }

    public function test_sales_order_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SalesOrder::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $salesOrder = $company->salesOrders()->inRandomOrder()->first();

        $salesOrderArr = SalesOrder::factory()->make()->toArray();

        $result = $this->salesOrderActions->update($salesOrder, $salesOrderArr);

        $this->assertInstanceOf(SalesOrder::class, $result);
        $this->assertDatabaseHas('sales_orders', [
            'id' => $salesOrder->id,
            'company_id' => $salesOrder->company_id,
            'code' => $salesOrderArr['code'],
            'name' => $salesOrderArr['name'],
        ]);
    }

    public function test_sales_order_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SalesOrder::factory())
            )->create();

        $salesOrder = $user->companies()->inRandomOrder()->first()
            ->salesOrders()->inRandomOrder()->first();

        $salesOrderArr = [];

        $this->salesOrderActions->update($salesOrder, $salesOrderArr);
    }
}
