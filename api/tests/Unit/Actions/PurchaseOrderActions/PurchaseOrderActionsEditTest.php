<?php

namespace Tests\Unit\Actions\PurchaseOrderActions;

use App\Actions\PurchaseOrder\PurchaseOrderActions;
use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseOrderActionsEditTest extends ActionsTestCase
{
    private PurchaseOrderActions $purchaseOrderActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseOrderActions = new PurchaseOrderActions();
    }

    public function test_purchase_order_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseOrder::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseOrder = $company->purchaseOrders()->inRandomOrder()->first();

        $purchaseOrderArr = PurchaseOrder::factory()->make()->toArray();

        $result = $this->purchaseOrderActions->update($purchaseOrder, $purchaseOrderArr);

        $this->assertInstanceOf(PurchaseOrder::class, $result);
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $purchaseOrder->id,
            'company_id' => $purchaseOrder->company_id,
            'code' => $purchaseOrderArr['code'],
            'name' => $purchaseOrderArr['name'],
        ]);
    }

    public function test_purchase_order_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseOrder::factory())
            )->create();

        $purchaseOrder = $user->companies()->inRandomOrder()->first()
            ->purchaseOrders()->inRandomOrder()->first();

        $purchaseOrderArr = [];

        $this->purchaseOrderActions->update($purchaseOrder, $purchaseOrderArr);
    }
}
