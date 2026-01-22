<?php

namespace Tests\Unit\Actions\SalePaymentActions;

use App\Actions\SalePayment\SalePaymentActions;
use App\Models\Company;
use App\Models\SalePayment;
use App\Models\User;
use Tests\ActionsTestCase;

class SalePaymentActionsDeleteTest extends ActionsTestCase
{
    private SalePaymentActions $salePaymentActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->salePaymentActions = new SalePaymentActions();
    }

    public function test_sale_payment_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SalePayment::factory())
            )->create();

        $salePayment = $user->companies()->inRandomOrder()->first()
            ->salePayments()->inRandomOrder()->first();
        $result = $this->salePaymentActions->delete($salePayment);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('sale_payments', [
            'id' => $salePayment->id,
        ]);
    }
}
