<?php

namespace Tests\Unit\Actions\PurchaseReceiptActions;

use App\Actions\PurchaseReceipt\PurchaseReceiptActions;
use App\Models\Company;
use App\Models\PurchaseReceipt;
use App\Models\User;
use Tests\ActionsTestCase;

class PurchaseReceiptActionsDeleteTest extends ActionsTestCase
{
    private PurchaseReceiptActions $purchaseReceiptActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReceiptActions = new PurchaseReceiptActions();
    }

    public function test_purchase_receipt_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(PurchaseReceipt::factory())
            )->create();

        $purchaseReceipt = $user->companies()->inRandomOrder()->first()
            ->purchaseReceipts()->inRandomOrder()->first();
        $result = $this->purchaseReceiptActions->delete($purchaseReceipt);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('purchase_receipts', [
            'id' => $purchaseReceipt->id,
        ]);
    }
}
