<?php

namespace Tests\Unit\Actions\SaleReceiptActions;

use App\Actions\SaleReceipt\SaleReceiptActions;
use App\Models\Company;
use App\Models\SaleReceipt;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class SaleReceiptActionsCreateTest extends ActionsTestCase
{
    private SaleReceiptActions $saleReceiptActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleReceiptActions = new SaleReceiptActions();
    }

    public function test_sale_receipt_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $saleReceiptArr = SaleReceipt::factory()->for($company)
            ->make()->toArray();

        $result = $this->saleReceiptActions->create($saleReceiptArr);

        $this->assertDatabaseHas('sale_receipts', [
            'id' => $result->id,
            'company_id' => $saleReceiptArr['company_id'],
            'code' => $saleReceiptArr['code'],
            'name' => $saleReceiptArr['name'],
        ]);
    }

    public function test_sale_receipt_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->saleReceiptActions->create([]);
    }
}
