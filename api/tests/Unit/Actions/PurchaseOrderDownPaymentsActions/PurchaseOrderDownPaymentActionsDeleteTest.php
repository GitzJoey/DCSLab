<?php

namespace Tests\Unit\Actions\PurchaseOrderDownPaymentActions;

use App\Actions\PurchaseOrderDownPayment\PurchaseOrderDownPaymentActions;
use App\Models\Company;
use App\Models\PurchaseOrderDownPayment;
use App\Models\User;
use Tests\ActionsTestCase;

class PurchaseOrderDownPaymentActionsDeleteTest extends ActionsTestCase
{
    private PurchaseOrderDownPaymentActions $purchaseOrderDownPaymentActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseOrderDownPaymentActions = new PurchaseOrderDownPaymentActions();
    }

    public function test_purchase_order_down_payments_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseOrderDownPayment::factory())
            )->create();

        $purchaseOrderDownPayment = $user->companies()->inRandomOrder()->first()
            ->purchaseOrderDownPayment()->inRandomOrder()->first();
        $result = $this->purchaseOrderDownPaymentActions->delete($purchaseOrderDownPayment);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('purchase_order_down_payments', [
            'id' => $purchaseOrderDownPayment->id,
        ]);
    }
}
