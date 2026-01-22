<?php

namespace Tests\Unit\Actions\PurchaseReturnAdditionalCostActions;

use App\Actions\PurchaseReturnAdditionalCost\PurchaseReturnAdditionalCostActions;
use App\Models\Company;
use App\Models\PurchaseReturnAdditionalCost;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseReturnAdditionalCostActionsCreateTest extends ActionsTestCase
{
    private PurchaseReturnAdditionalCostActions $purchaseReturnAdditionalCostActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseReturnAdditionalCostActions = new PurchaseReturnAdditionalCostActions();
    }

    public function test_purchase_return_additional_cost_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $purchaseReturnAdditionalCostArr = PurchaseReturnAdditionalCost::factory()->for($company)
            ->make()->toArray();

        $result = $this->purchaseReturnAdditionalCostActions->create($purchaseReturnAdditionalCostArr);

        $this->assertDatabaseHas('purchase_return_additional_costs', [
            'id' => $result->id,
            'company_id' => $purchaseReturnAdditionalCostArr['company_id'],
            'code' => $purchaseReturnAdditionalCostArr['code'],
            'name' => $purchaseReturnAdditionalCostArr['name'],
        ]);
    }

    public function test_purchase_return_additional_cost_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->purchaseReturnAdditionalCostActions->create([]);
    }
}
