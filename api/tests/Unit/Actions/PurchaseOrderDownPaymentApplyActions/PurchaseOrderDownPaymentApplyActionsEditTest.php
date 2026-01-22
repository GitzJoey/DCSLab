<?php

namespace Tests\Unit\Actions\PurchaseOrderDownPaymentApplyActions;

use App\Actions\PurchaseOrderDownPaymentApply\PurchaseOrderDownPaymentApplyActions;
use App\Models\Company;
use App\Models\PurchaseOrderDownPaymentApply;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseOrderDownPaymentApplyActionsEditTest extends ActionsTestCase
{
    private PurchaseOrderDownPaymentApplyActions $purchaseOrderDownPaymentApplyActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseOrderDownPaymentApplyActions = new PurchaseOrderDownPaymentApplyActions();
    }

    public function test_purchase_order_down_payment_apply_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseOrderDownPaymentApply::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseOrderDownPaymentApply = $company->purchaseOrderDownPaymentApplies()->inRandomOrder()->first();

        $purchaseOrderDownPaymentApplyArr = PurchaseOrderDownPaymentApply::factory()->make()->toArray();

        $result = $this->purchaseOrderDownPaymentApplyActions->update($purchaseOrderDownPaymentApply, $purchaseOrderDownPaymentApplyArr);

        $this->assertInstanceOf(PurchaseOrderDownPaymentApply::class, $result);
        $this->assertDatabaseHas('purchase_order_down_payment_applies', [
            'id' => $purchaseOrderDownPaymentApply->id,
            'company_id' => $purchaseOrderDownPaymentApply->company_id,
            'code' => $purchaseOrderDownPaymentApplyArr['code'],
            'name' => $purchaseOrderDownPaymentApplyArr['name'],
        ]);
    }

    public function test_purchase_order_down_payment_apply_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseOrderDownPaymentApply::factory())
            )->create();

        $purchaseOrderDownPaymentApply = $user->companies()->inRandomOrder()->first()
            ->purchaseOrderDownPaymentApplies()->inRandomOrder()->first();

        $purchaseOrderDownPaymentApplyArr = [];

        $this->purchaseOrderDownPaymentApplyActions->update($purchaseOrderDownPaymentApply, $purchaseOrderDownPaymentApplyArr);
    }
}
