<?php

namespace Tests\Unit\Actions\PurchaseOrderDownPaymentActions;

use App\Actions\PurchaseOrderDownPayment\PurchaseOrderDownPaymentActions;
use App\Models\Company;
use App\Models\PurchaseOrderDownPayment;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseOrderDownPaymentActionsCreateTest extends ActionsTestCase
{
    private PurchaseOrderDownPaymentActions $purchaseOrderDownPaymentActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseOrderDownPaymentActions = new PurchaseOrderDownPaymentActions();
    }

    public function test_purchase_order_down_payments_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $purchaseOrderDownPaymentArr = PurchaseOrderDownPayment::factory()->for($company)
            ->make()->toArray();

        $result = $this->purchaseOrderDownPaymentActions->create($purchaseOrderDownPaymentArr);

        $this->assertDatabaseHas('purchase_order_down_payments', [
            'id' => $result->id,
            'company_id' => $purchaseOrderDownPaymentArr['company_id'],
            'code' => $purchaseOrderDownPaymentArr['code'],
            'name' => $purchaseOrderDownPaymentArr['name'],
        ]);
    }

    public function test_purchase_order_down_payments_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->purchaseOrderDownPaymentActions->create([]);
    }
}
