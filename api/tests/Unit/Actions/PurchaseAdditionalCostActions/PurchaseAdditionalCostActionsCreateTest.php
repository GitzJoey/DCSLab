<?php

namespace Tests\Unit\Actions\PurchaseAdditionalCostActions;

use App\Actions\PurchaseAdditionalCost\PurchaseAdditionalCostActions;
use App\Models\Company;
use App\Models\PurchaseAdditionalCost;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseAdditionalCostActionsCreateTest extends ActionsTestCase
{
    private PurchaseAdditionalCostActions $purchaseAdditionalCostActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseAdditionalCostActions = new PurchaseAdditionalCostActions();
    }

    public function test_purchase_additional_cost_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $purchaseAdditionalCostArr = PurchaseAdditionalCost::factory()->for($company)
            ->make()->toArray();

        $result = $this->purchaseAdditionalCostActions->create($purchaseAdditionalCostArr);

        $this->assertDatabaseHas('purchase_additional_costs', [
            'id' => $result->id,
            'company_id' => $purchaseAdditionalCostArr['company_id'],
            'code' => $purchaseAdditionalCostArr['code'],
            'name' => $purchaseAdditionalCostArr['name'],
        ]);
    }

    public function test_purchase_additional_cost_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->purchaseAdditionalCostActions->create([]);
    }
}
