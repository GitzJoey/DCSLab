<?php

namespace Tests\Unit\Actions\PurchaseActions;

use App\Actions\Purchase\PurchaseActions;
use App\Models\Company;
use App\Models\Purchase;
use App\Models\User;
use Tests\ActionsTestCase;

class PurchaseActionsDeleteTest extends ActionsTestCase
{
    private PurchaseActions $purchaseActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseActions = new PurchaseActions();
    }

    public function test_purchase_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Purchase::factory())
            )->create();

        $purchase = $user->companies()->inRandomOrder()->first()
            ->purchases()->inRandomOrder()->first();
        $result = $this->purchaseActions->delete($purchase);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('purchases', [
            'id' => $purchase->id,
        ]);
    }
}
