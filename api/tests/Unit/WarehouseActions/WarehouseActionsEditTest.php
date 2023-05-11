<?php

namespace Tests\Unit;

use App\Actions\Warehouse\WarehouseActions;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\Warehouse;
use Exception;

use Tests\ActionsTestCase;

class WarehouseActionsEditTest extends ActionsTestCase
{
    private WarehouseActions $warehouseActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->warehouseActions = new WarehouseActions();
    }

    public function test_warehouse_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(
                    Branch::factory()->setStatusActive()->setIsMainBranch()
                ))->create();

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $warehouse = Warehouse::factory()->for($company)->for($branch)->create();

        $warehouseArr = Warehouse::factory()->make()->toArray();

        $result = $this->warehouseActions->update($warehouse, $warehouseArr);

        $this->assertInstanceOf(Warehouse::class, $result);
        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'company_id' => $warehouse->company_id,
            'branch_id' => $warehouse->branch_id,
            'code' => $warehouseArr['code'],
            'name' => $warehouseArr['name'],
            'address' => $warehouseArr['address'],
            'city' => $warehouseArr['city'],
            'contact' => $warehouseArr['contact'],
            'remarks' => $warehouseArr['remarks'],
            'status' => $warehouseArr['status'],
        ]);
    }

    public function test_warehouse_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(
                    Branch::factory()->setStatusActive()->setIsMainBranch()
                ))->create();

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $warehouse = Warehouse::factory()->for($company)->for($branch)->create();

        $warehouseArr = [];

        $this->warehouseActions->update($warehouse, $warehouseArr);
    }
}
