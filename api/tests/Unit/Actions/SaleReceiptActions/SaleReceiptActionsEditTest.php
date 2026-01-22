<?php

namespace Tests\Unit\Actions\SaleReceiptActions;

use App\Actions\SaleReceipt\SaleReceiptActions;
use App\Models\Company;
use App\Models\SaleReceipt;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SaleReceiptActionsEditTest extends ActionsTestCase
{
    private SaleReceiptActions $saleReceiptActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleReceiptActions = new SaleReceiptActions();
    }

    public function test_sale_receipt_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleReceipt::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $saleReceipt = $company->saleReceipts()->inRandomOrder()->first();

        $saleReceiptArr = SaleReceipt::factory()->make()->toArray();

        $result = $this->saleReceiptActions->update($saleReceipt, $saleReceiptArr);

        $this->assertInstanceOf(SaleReceipt::class, $result);
        $this->assertDatabaseHas('sale_receipts', [
            'id' => $saleReceipt->id,
            'company_id' => $saleReceipt->company_id,
            'code' => $saleReceiptArr['code'],
            'name' => $saleReceiptArr['name'],
        ]);
    }

    public function test_sale_receipt_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleReceipt::factory())
            )->create();

        $saleReceipt = $user->companies()->inRandomOrder()->first()
            ->saleReceipts()->inRandomOrder()->first();

        $saleReceiptArr = [];

        $this->saleReceiptActions->update($saleReceipt, $saleReceiptArr);
    }
}
