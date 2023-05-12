<?php

namespace Tests\Unit\WarehouseActions;

use App\Actions\Warehouse\WarehouseActions;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\Warehouse;
use Exception;
use Tests\ActionsTestCase;

class WarehouseActionsCreateTest extends ActionsTestCase
{
    private WarehouseActions $warehouseActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->warehouseActions = new WarehouseActions();
    }

    public function test_warehouse_actions_call_create_expect_db_has_record_d()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        $warehouseArr = Warehouse::factory()
            ->for($company)
            ->for($branch)
            ->make()->toArray();

        $result = $this->warehouseActions->create($warehouseArr);

        $this->assertDatabaseHas('warehouses', [
            'id' => $result->id,
            'company_id' => $warehouseArr['company_id'],
            'branch_id' => $warehouseArr['branch_id'],
            'code' => $warehouseArr['code'],
            'name' => $warehouseArr['name'],
            'address' => $warehouseArr['address'],
            'city' => $warehouseArr['city'],
            'contact' => $warehouseArr['contact'],
            'remarks' => $warehouseArr['remarks'],
            'status' => $warehouseArr['status'],
        ]);
    }

    public function test_warehouse_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->warehouseActions->create([]);
    }
}
