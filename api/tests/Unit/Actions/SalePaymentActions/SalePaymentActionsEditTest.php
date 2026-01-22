<?php

namespace Tests\Unit\Actions\SalePaymentActions;

use App\Actions\SalePayment\SalePaymentActions;
use App\Models\Company;
use App\Models\SalePayment;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SalePaymentActionsEditTest extends ActionsTestCase
{
    private SalePaymentActions $salePaymentActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->salePaymentActions = new SalePaymentActions();
    }

    public function test_sale_payment_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SalePayment::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $salePayment = $company->salePayments()->inRandomOrder()->first();

        $salePaymentArr = SalePayment::factory()->make()->toArray();

        $result = $this->salePaymentActions->update($salePayment, $salePaymentArr);

        $this->assertInstanceOf(SalePayment::class, $result);
        $this->assertDatabaseHas('sale_payments', [
            'id' => $salePayment->id,
            'company_id' => $salePayment->company_id,
            'code' => $salePaymentArr['code'],
            'name' => $salePaymentArr['name'],
        ]);
    }

    public function test_sale_payment_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SalePayment::factory())
            )->create();

        $salePayment = $user->companies()->inRandomOrder()->first()
            ->salePayments()->inRandomOrder()->first();

        $salePaymentArr = [];

        $this->salePaymentActions->update($salePayment, $salePaymentArr);
    }
}
