<?php

namespace Tests\Unit\Actions\PurchaseActions;

use App\Actions\Purchase\PurchaseActions;
use App\Models\Company;
use App\Models\Purchase;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseActionsEditTest extends ActionsTestCase
{
    private PurchaseActions $purchaseActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseActions = new PurchaseActions();
    }

    public function test_purchase_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Purchase::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchase = $company->purchases()->inRandomOrder()->first();

        $purchaseArr = Purchase::factory()->make()->toArray();

        $result = $this->purchaseActions->update($purchase, $purchaseArr);

        $this->assertInstanceOf(Purchase::class, $result);
        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'company_id' => $purchase->company_id,
            'code' => $purchaseArr['code'],
            'name' => $purchaseArr['name'],
        ]);
    }

    public function test_purchase_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Purchase::factory())
            )->create();

        $purchase = $user->companies()->inRandomOrder()->first()
            ->purchases()->inRandomOrder()->first();

        $purchaseArr = [];

        $this->purchaseActions->update($purchase, $purchaseArr);
    }
}
