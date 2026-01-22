<?php

namespace Tests\Unit\Actions\PurchaseOrderDownPaymentActions;

use App\Actions\PurchaseOrderDownPayment\PurchaseOrderDownPaymentActions;
use App\Models\Company;
use App\Models\PurchaseOrderDownPayment;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseOrderDownPaymentActionsEditTest extends ActionsTestCase
{
    private PurchaseOrderDownPaymentActions $purchaseOrderDownPaymentActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseOrderDownPaymentActions = new PurchaseOrderDownPaymentActions();
    }

    public function test_purchase_order_down_payments_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseOrderDownPayment::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseOrderDownPayment = $company->purchaseOrderDownPayment()->inRandomOrder()->first();

        $purchaseOrderDownPaymentArr = PurchaseOrderDownPayment::factory()->make()->toArray();

        $result = $this->purchaseOrderDownPaymentActions->update($purchaseOrderDownPayment, $purchaseOrderDownPaymentArr);

        $this->assertInstanceOf(PurchaseOrderDownPayment::class, $result);
        $this->assertDatabaseHas('purchase_order_down_payments', [
            'id' => $purchaseOrderDownPayment->id,
            'company_id' => $purchaseOrderDownPayment->company_id,
            'code' => $purchaseOrderDownPaymentArr['code'],
            'name' => $purchaseOrderDownPaymentArr['name'],
        ]);
    }

    public function test_purchase_order_down_payments_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseOrderDownPayment::factory())
            )->create();

        $purchaseOrderDownPayment = $user->companies()->inRandomOrder()->first()
            ->purchaseOrderDownPayment()->inRandomOrder()->first();

        $purchaseOrderDownPaymentArr = [];

        $this->purchaseOrderDownPaymentActions->update($purchaseOrderDownPayment, $purchaseOrderDownPaymentArr);
    }
}
