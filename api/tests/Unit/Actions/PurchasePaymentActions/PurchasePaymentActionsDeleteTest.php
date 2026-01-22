<?php

namespace Tests\Unit\Actions\PurchasePaymentActions;

use App\Actions\PurchasePayment\PurchasePaymentActions;
use App\Models\Company;
use App\Models\PurchasePayment;
use App\Models\User;
use Tests\ActionsTestCase;

class PurchasePaymentActionsDeleteTest extends ActionsTestCase
{
    private PurchasePaymentActions $purchasePaymentActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchasePaymentActions = new PurchasePaymentActions();
    }

    public function test_purchase_payment_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchasePayment::factory())
            )->create();

        $purchasePayment = $user->companies()->inRandomOrder()->first()
            ->purchasePayments()->inRandomOrder()->first();
        $result = $this->purchasePaymentActions->delete($purchasePayment);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('purchase_payments', [
            'id' => $purchasePayment->id,
        ]);
    }
}
