<?php

namespace Tests\Unit\Actions\WarehouseActions;

use App\Actions\Warehouse\WarehouseActions;
use App\DTOs\WarehouseUpdateDTO;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\Warehouse;
use ArgumentCountError;
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
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
            )
            ->create();

        $company = $user->companies()->first();
        $branch = $company->branches()->first();
        $warehouse = Warehouse::factory()->for($company)->for($branch)->create();
        $payload = Warehouse::factory()->make()->toArray();

        $dto = new WarehouseUpdateDTO(
            code: $payload['code'],
            name: $payload['name'],
            address: $payload['address'],
            city: $payload['city'],
            contact: $payload['contact'],
            remarks: $payload['remarks'],
            status: $payload['status'],
        );

        $result = $this->warehouseActions->update($warehouse, $dto);
        $this->assertInstanceOf(Warehouse::class, $result);
        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'company_id' => $warehouse->company_id,
            'branch_id' => $warehouse->branch_id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_warehouse_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(ArgumentCountError::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
            )
            ->create();

        $warehouse = Warehouse::factory()
            ->for($user->companies()->first())
            ->for($user->companies()->first()->branches()->first())
            ->create();

        $dto = new WarehouseUpdateDTO(...[]);

        $this->warehouseActions->update($warehouse, $dto);
    }
}
