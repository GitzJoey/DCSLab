<?php

namespace Tests\Unit\Actions\PurchaseActions;

use App\Actions\Purchase\PurchaseActions;
use App\Models\Company;
use App\Models\Purchase;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseActionsCreateTest extends ActionsTestCase
{
    private PurchaseActions $purchaseActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseActions = new PurchaseActions();
    }

    public function test_purchase_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $purchaseArr = Purchase::factory()->for($company)
            ->make()->toArray();

        $result = $this->purchaseActions->create($purchaseArr);

        $this->assertDatabaseHas('purchases', [
            'id' => $result->id,
            'company_id' => $purchaseArr['company_id'],
            'code' => $purchaseArr['code'],
            'name' => $purchaseArr['name'],
        ]);
    }

    public function test_purchase_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->purchaseActions->create([]);
    }
}
