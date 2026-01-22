<?php

namespace Tests\Unit\Actions\SaleOrderDownPaymentApplyActions;

use App\Actions\SaleOrderDownPaymentApply\SaleOrderDownPaymentApplyActions;
use App\Models\Company;
use App\Models\SaleOrderDownPaymentApply;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SaleOrderDownPaymentApplyActionsEditTest extends ActionsTestCase
{
    private SaleOrderDownPaymentApplyActions $saleOrderDownPaymentApplyActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleOrderDownPaymentApplyActions = new SaleOrderDownPaymentApplyActions();
    }

    public function test_sale_order_down_payment_apply_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleOrderDownPaymentApply::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $saleOrderDownPaymentApply = $company->saleOrderDownPaymentApplies()->inRandomOrder()->first();

        $saleOrderDownPaymentApplyArr = SaleOrderDownPaymentApply::factory()->make()->toArray();

        $result = $this->saleOrderDownPaymentApplyActions->update($saleOrderDownPaymentApply, $saleOrderDownPaymentApplyArr);

        $this->assertInstanceOf(SaleOrderDownPaymentApply::class, $result);
        $this->assertDatabaseHas('sale_order_down_payment_applies', [
            'id' => $saleOrderDownPaymentApply->id,
            'company_id' => $saleOrderDownPaymentApply->company_id,
            'code' => $saleOrderDownPaymentApplyArr['code'],
            'name' => $saleOrderDownPaymentApplyArr['name'],
        ]);
    }

    public function test_sale_order_down_payment_apply_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleOrderDownPaymentApply::factory())
            )->create();

        $saleOrderDownPaymentApply = $user->companies()->inRandomOrder()->first()
            ->saleOrderDownPaymentApplies()->inRandomOrder()->first();

        $saleOrderDownPaymentApplyArr = [];

        $this->saleOrderDownPaymentApplyActions->update($saleOrderDownPaymentApply, $saleOrderDownPaymentApplyArr);
    }
}
