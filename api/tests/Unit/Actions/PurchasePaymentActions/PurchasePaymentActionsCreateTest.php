<?php

namespace Tests\Unit\Actions\PurchasePaymentActions;

use App\Actions\PurchasePayment\PurchasePaymentActions;
use App\Models\Company;
use App\Models\PurchasePayment;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchasePaymentActionsCreateTest extends ActionsTestCase
{
    private PurchasePaymentActions $purchasePaymentActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchasePaymentActions = new PurchasePaymentActions();
    }

    public function test_purchase_payment_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $purchasePaymentArr = PurchasePayment::factory()->for($company)
            ->make()->toArray();

        $result = $this->purchasePaymentActions->create($purchasePaymentArr);

        $this->assertDatabaseHas('purchase_payments', [
            'id' => $result->id,
            'company_id' => $purchasePaymentArr['company_id'],
            'code' => $purchasePaymentArr['code'],
            'name' => $purchasePaymentArr['name'],
        ]);
    }

    public function test_purchase_payment_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->purchasePaymentActions->create([]);
    }
}
