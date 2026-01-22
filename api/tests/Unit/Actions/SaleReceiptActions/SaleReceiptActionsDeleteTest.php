<?php

namespace Tests\Unit\Actions\SaleReceiptActions;

use App\Actions\SaleReceipt\SaleReceiptActions;
use App\Models\Company;
use App\Models\SaleReceipt;
use App\Models\User;
use Tests\ActionsTestCase;

class SaleReceiptActionsDeleteTest extends ActionsTestCase
{
    private SaleReceiptActions $saleReceiptActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleReceiptActions = new SaleReceiptActions();
    }

    public function test_sale_receipt_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(SaleReceipt::factory())
            )->create();

        $saleReceipt = $user->companies()->inRandomOrder()->first()
            ->saleReceipts()->inRandomOrder()->first();
        $result = $this->saleReceiptActions->delete($saleReceipt);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('sale_receipts', [
            'id' => $saleReceipt->id,
        ]);
    }
}
