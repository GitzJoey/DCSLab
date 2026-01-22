<?php

namespace Tests\Unit\Actions\PurchaseOrderActions;

use App\Actions\PurchaseOrder\PurchaseOrderActions;
use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\User;
use Tests\ActionsTestCase;

class PurchaseOrderActionsDeleteTest extends ActionsTestCase
{
    private PurchaseOrderActions $purchaseOrderActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseOrderActions = new PurchaseOrderActions();
    }

    public function test_purchase_order_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseOrder::factory())
            )->create();

        $purchaseOrder = $user->companies()->inRandomOrder()->first()
            ->purchaseOrders()->inRandomOrder()->first();
        $result = $this->purchaseOrderActions->delete($purchaseOrder);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('purchase_orders', [
            'id' => $purchaseOrder->id,
        ]);
    }
}
