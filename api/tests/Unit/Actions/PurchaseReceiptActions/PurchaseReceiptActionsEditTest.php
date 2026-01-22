<?php

namespace Tests\Unit\Actions\PurchaseReceiptActions;

use App\Actions\PurchaseReceipt\PurchaseReceiptActions;
use App\Models\Company;
use App\Models\PurchaseReceipt;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseReceiptActionsEditTest extends ActionsTestCase
{
    private PurchaseReceiptActions $purchaseReceiptActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReceiptActions = new PurchaseReceiptActions();
    }

    public function test_purchase_receipt_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReceipt::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseReceipt = $company->purchaseReceipts()->inRandomOrder()->first();

        $purchaseReceiptArr = PurchaseReceipt::factory()->make()->toArray();

        $result = $this->purchaseReceiptActions->update($purchaseReceipt, $purchaseReceiptArr);

        $this->assertInstanceOf(PurchaseReceipt::class, $result);
        $this->assertDatabaseHas('purchase_receipts', [
            'id' => $purchaseReceipt->id,
            'company_id' => $purchaseReceipt->company_id,
            'code' => $purchaseReceiptArr['code'],
            'name' => $purchaseReceiptArr['name'],
        ]);
    }

    public function test_purchase_receipt_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReceipt::factory())
            )->create();

        $purchaseReceipt = $user->companies()->inRandomOrder()->first()
            ->purchaseReceipts()->inRandomOrder()->first();

        $purchaseReceiptArr = [];

        $this->purchaseReceiptActions->update($purchaseReceipt, $purchaseReceiptArr);
    }
}
