<?php

namespace Tests\Unit\Actions\PurchaseOrderDownPaymentApplyActions;

use App\Actions\PurchaseOrderDownPaymentApply\PurchaseOrderDownPaymentApplyActions;
use App\Models\Company;
use App\Models\PurchaseOrderDownPaymentApply;
use App\Models\User;
use Tests\ActionsTestCase;

class PurchaseOrderDownPaymentApplyActionsDeleteTest extends ActionsTestCase
{
    private PurchaseOrderDownPaymentApplyActions $purchaseOrderDownPaymentApplyActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseOrderDownPaymentApplyActions = new PurchaseOrderDownPaymentApplyActions();
    }

    public function test_purchase_order_down_payment_apply_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseOrderDownPaymentApply::factory())
            )->create();

        $purchaseOrderDownPaymentApply = $user->companies()->inRandomOrder()->first()
            ->purchaseOrderDownPaymentApplies()->inRandomOrder()->first();
        $result = $this->purchaseOrderDownPaymentApplyActions->delete($purchaseOrderDownPaymentApply);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('purchase_order_down_payment_applies', [
            'id' => $purchaseOrderDownPaymentApply->id,
        ]);
    }
}
