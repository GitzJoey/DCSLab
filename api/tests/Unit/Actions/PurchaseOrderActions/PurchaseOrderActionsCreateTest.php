<?php

namespace Tests\Unit\Actions\PurchaseOrderActions;

use App\Actions\PurchaseOrder\PurchaseOrderActions;
use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class PurchaseOrderActionsCreateTest extends ActionsTestCase
{
    private PurchaseOrderActions $purchaseOrderActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->purchaseOrderActions = new PurchaseOrderActions();
    }

    public function test_purchase_order_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $purchaseOrderArr = PurchaseOrder::factory()->for($company)
            ->make()->toArray();

        $result = $this->purchaseOrderActions->create($purchaseOrderArr);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $result->id,
            'company_id' => $purchaseOrderArr['company_id'],
            'code' => $purchaseOrderArr['code'],
            'name' => $purchaseOrderArr['name'],
        ]);
    }

    public function test_purchase_order_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->purchaseOrderActions->create([]);
    }
}
