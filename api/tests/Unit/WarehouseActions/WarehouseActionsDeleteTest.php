<?php

namespace Tests\Unit\WarehouseActions;

use App\Actions\Warehouse\WarehouseActions;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\Warehouse;

use Tests\ActionsTestCase;

class WarehouseActionsDeleteTest extends ActionsTestCase
{
    private WarehouseActions $warehouseActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->warehouseActions = new WarehouseActions();
    }

    public function test_warehouse_actions_call_delete_expect_bool_n()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(
                    Branch::factory()->setStatusActive()->setIsMainBranch()
                ))->create();

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $warehouse = Warehouse::factory()->for($company)->for($branch)->create();

        $result = $this->warehouseActions->delete($warehouse);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('warehouses', [
            'id' => $warehouse->id,
        ]);
    }
}
