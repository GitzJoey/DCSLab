<?php

namespace Tests\Unit\Actions\SalePaymentActions;

use App\Actions\SalePayment\SalePaymentActions;
use App\Models\Company;
use App\Models\SalePayment;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SalePaymentActionsCreateTest extends ActionsTestCase
{
    private SalePaymentActions $salePaymentActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->salePaymentActions = new SalePaymentActions();
    }

    public function test_sale_payment_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $salePaymentArr = SalePayment::factory()->for($company)
            ->make()->toArray();

        $result = $this->salePaymentActions->create($salePaymentArr);

        $this->assertDatabaseHas('sale_payments', [
            'id' => $result->id,
            'company_id' => $salePaymentArr['company_id'],
            'code' => $salePaymentArr['code'],
            'name' => $salePaymentArr['name'],
        ]);
    }

    public function test_sale_payment_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->salePaymentActions->create([]);
    }
}
