<?php

namespace Tests\Unit\Actions\SaleOrderDownPaymentApplyActions;

use App\Actions\SaleOrderDownPaymentApply\SaleOrderDownPaymentApplyActions;
use App\Models\Company;
use App\Models\SaleOrderDownPaymentApply;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SaleOrderDownPaymentApplyActionsCreateTest extends ActionsTestCase
{
    private SaleOrderDownPaymentApplyActions $saleOrderDownPaymentApplyActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleOrderDownPaymentApplyActions = new SaleOrderDownPaymentApplyActions();
    }

    public function test_sale_order_down_payment_apply_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $saleOrderDownPaymentApplyArr = SaleOrderDownPaymentApply::factory()->for($company)
            ->make()->toArray();

        $result = $this->saleOrderDownPaymentApplyActions->create($saleOrderDownPaymentApplyArr);

        $this->assertDatabaseHas('sale_order_down_payment_applies', [
            'id' => $result->id,
            'company_id' => $saleOrderDownPaymentApplyArr['company_id'],
            'code' => $saleOrderDownPaymentApplyArr['code'],
            'name' => $saleOrderDownPaymentApplyArr['name'],
        ]);
    }

    public function test_sale_order_down_payment_apply_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->saleOrderDownPaymentApplyActions->create([]);
    }
}
