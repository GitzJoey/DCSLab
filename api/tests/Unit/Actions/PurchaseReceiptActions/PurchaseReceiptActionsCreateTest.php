<?php

namespace Tests\Unit\Actions\PurchaseReceiptActions;

use App\Actions\PurchaseReceipt\PurchaseReceiptActions;
use App\Models\Company;
use App\Models\PurchaseReceipt;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseReceiptActionsCreateTest extends ActionsTestCase
{
    private PurchaseReceiptActions $purchaseReceiptActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReceiptActions = new PurchaseReceiptActions();
    }

    public function test_purchase_receipt_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $purchaseReceiptArr = PurchaseReceipt::factory()->for($company)
            ->make()->toArray();

        $result = $this->purchaseReceiptActions->create($purchaseReceiptArr);

        $this->assertDatabaseHas('purchase_receipts', [
            'id' => $result->id,
            'company_id' => $purchaseReceiptArr['company_id'],
            'code' => $purchaseReceiptArr['code'],
            'name' => $purchaseReceiptArr['name'],
        ]);
    }

    public function test_purchase_receipt_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->purchaseReceiptActions->create([]);
    }
}
