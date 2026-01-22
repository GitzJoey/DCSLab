<?php

namespace Tests\Unit\Actions\SalesOrderActions;

use App\Actions\SalesOrder\SalesOrderActions;
use App\Models\Company;
use App\Models\SalesOrder;
use App\Models\User;
use Tests\ActionsTestCase;

class SalesOrderActionsDeleteTest extends ActionsTestCase
{
    private SalesOrderActions $salesOrderActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->salesOrderActions = new SalesOrderActions();
    }

    public function test_sales_order_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SalesOrder::factory())
            )->create();

        $salesOrder = $user->companies()->inRandomOrder()->first()
            ->salesOrders()->inRandomOrder()->first();
        $result = $this->salesOrderActions->delete($salesOrder);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('sales_orders', [
            'id' => $salesOrder->id,
        ]);
    }
}
