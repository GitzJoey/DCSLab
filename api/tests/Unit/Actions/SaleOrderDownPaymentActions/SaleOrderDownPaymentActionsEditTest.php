<?php

namespace Tests\Unit\Actions\SaleOrderDownPaymentActions;

use App\Actions\SaleOrderDownPayment\SaleOrderDownPaymentActions;
use App\Models\Company;
use App\Models\SaleOrderDownPayment;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SaleOrderDownPaymentActionsEditTest extends ActionsTestCase
{
    private SaleOrderDownPaymentActions $saleOrderDownPaymentActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleOrderDownPaymentActions = new SaleOrderDownPaymentActions();
    }

    public function test_sale_order_down_payment_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleOrderDownPayment::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $saleOrderDownPayment = $company->saleOrderDownPayments()->inRandomOrder()->first();

        $saleOrderDownPaymentArr = SaleOrderDownPayment::factory()->make()->toArray();

        $result = $this->saleOrderDownPaymentActions->update($saleOrderDownPayment, $saleOrderDownPaymentArr);

        $this->assertInstanceOf(SaleOrderDownPayment::class, $result);
        $this->assertDatabaseHas('sale_order_down_payments', [
            'id' => $saleOrderDownPayment->id,
            'company_id' => $saleOrderDownPayment->company_id,
            'code' => $saleOrderDownPaymentArr['code'],
            'name' => $saleOrderDownPaymentArr['name'],
        ]);
    }

    public function test_sale_order_down_payment_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleOrderDownPayment::factory())
            )->create();

        $saleOrderDownPayment = $user->companies()->inRandomOrder()->first()
            ->saleOrderDownPayments()->inRandomOrder()->first();

        $saleOrderDownPaymentArr = [];

        $this->saleOrderDownPaymentActions->update($saleOrderDownPayment, $saleOrderDownPaymentArr);
    }
}
