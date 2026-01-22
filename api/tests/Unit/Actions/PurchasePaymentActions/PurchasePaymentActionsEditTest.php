<?php

namespace Tests\Unit\Actions\PurchasePaymentActions;

use App\Actions\PurchasePayment\PurchasePaymentActions;
use App\Models\Company;
use App\Models\PurchasePayment;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchasePaymentActionsEditTest extends ActionsTestCase
{
    private PurchasePaymentActions $purchasePaymentActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchasePaymentActions = new PurchasePaymentActions();
    }

    public function test_purchase_payment_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchasePayment::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchasePayment = $company->purchasePayments()->inRandomOrder()->first();

        $purchasePaymentArr = PurchasePayment::factory()->make()->toArray();

        $result = $this->purchasePaymentActions->update($purchasePayment, $purchasePaymentArr);

        $this->assertInstanceOf(PurchasePayment::class, $result);
        $this->assertDatabaseHas('purchase_payments', [
            'id' => $purchasePayment->id,
            'company_id' => $purchasePayment->company_id,
            'code' => $purchasePaymentArr['code'],
            'name' => $purchasePaymentArr['name'],
        ]);
    }

    public function test_purchase_payment_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchasePayment::factory())
            )->create();

        $purchasePayment = $user->companies()->inRandomOrder()->first()
            ->purchasePayments()->inRandomOrder()->first();

        $purchasePaymentArr = [];

        $this->purchasePaymentActions->update($purchasePayment, $purchasePaymentArr);
    }
}
