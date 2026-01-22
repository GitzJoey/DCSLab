<?php

namespace Tests\Unit\Actions\SaleOrderDownPaymentActions;

use App\Actions\SaleOrderDownPayment\SaleOrderDownPaymentActions;
use App\Models\Company;
use App\Models\SaleOrderDownPayment;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SaleOrderDownPaymentActionsCreateTest extends ActionsTestCase
{
    private SaleOrderDownPaymentActions $saleOrderDownPaymentActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleOrderDownPaymentActions = new SaleOrderDownPaymentActions();
    }

    public function test_sale_order_down_payment_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $saleOrderDownPaymentArr = SaleOrderDownPayment::factory()->for($company)
            ->make()->toArray();

        $result = $this->saleOrderDownPaymentActions->create($saleOrderDownPaymentArr);

        $this->assertDatabaseHas('sale_order_down_payments', [
            'id' => $result->id,
            'company_id' => $saleOrderDownPaymentArr['company_id'],
            'code' => $saleOrderDownPaymentArr['code'],
            'name' => $saleOrderDownPaymentArr['name'],
        ]);
    }

    public function test_sale_order_down_payment_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->saleOrderDownPaymentActions->create([]);
    }
}
