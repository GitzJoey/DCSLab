<?php

namespace Tests\Unit\Actions\PurchaseOrderDownPaymentApplyActions;

use App\Actions\PurchaseOrderDownPaymentApply\PurchaseOrderDownPaymentApplyActions;
use App\Models\Company;
use App\Models\PurchaseOrderDownPaymentApply;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseOrderDownPaymentApplyActionsCreateTest extends ActionsTestCase
{
    private PurchaseOrderDownPaymentApplyActions $purchaseOrderDownPaymentApplyActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseOrderDownPaymentApplyActions = new PurchaseOrderDownPaymentApplyActions();
    }

    public function test_purchase_order_down_payment_apply_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $purchaseOrderDownPaymentApplyArr = PurchaseOrderDownPaymentApply::factory()->for($company)
            ->make()->toArray();

        $result = $this->purchaseOrderDownPaymentApplyActions->create($purchaseOrderDownPaymentApplyArr);

        $this->assertDatabaseHas('purchase_order_down_payment_applies', [
            'id' => $result->id,
            'company_id' => $purchaseOrderDownPaymentApplyArr['company_id'],
            'code' => $purchaseOrderDownPaymentApplyArr['code'],
            'name' => $purchaseOrderDownPaymentApplyArr['name'],
        ]);
    }

    public function test_purchase_order_down_payment_apply_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->purchaseOrderDownPaymentApplyActions->create([]);
    }
}
