<?php

namespace Tests\Unit\Actions\SaleOrderDownPaymentActions;

use App\Actions\SaleOrderDownPayment\SaleOrderDownPaymentActions;
use App\Models\Company;
use App\Models\SaleOrderDownPayment;
use App\Models\User;
use Tests\ActionsTestCase;

class SaleOrderDownPaymentActionsDeleteTest extends ActionsTestCase
{
    private SaleOrderDownPaymentActions $saleOrderDownPaymentActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleOrderDownPaymentActions = new SaleOrderDownPaymentActions();
    }

    public function test_sale_order_down_payment_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleOrderDownPayment::factory())
            )->create();

        $saleOrderDownPayment = $user->companies()->inRandomOrder()->first()
            ->saleOrderDownPayments()->inRandomOrder()->first();
        $result = $this->saleOrderDownPaymentActions->delete($saleOrderDownPayment);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('sale_order_down_payments', [
            'id' => $saleOrderDownPayment->id,
        ]);
    }
}
