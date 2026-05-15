<?php

namespace Tests\Unit\Actions\WarehouseActions;

use App\Actions\Warehouse\WarehouseActions;
use App\DTOs\WarehouseCreateDTO;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\Warehouse;
use ArgumentCountError;
use Tests\ActionsTestCase;

class WarehouseActionsCreateTest extends ActionsTestCase
{
    private WarehouseActions $warehouseActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->warehouseActions = new WarehouseActions();
    }

    public function test_warehouse_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
            )
            ->create();

        $company = $user->companies()->first();
        $branch = $company->branches()->first();
        $payload = Warehouse::factory()->for($company)->for($branch)->make()->toArray();

        $dto = new WarehouseCreateDTO(
            companyId: $payload['company_id'],
            branchId: $payload['branch_id'],
            code: $payload['code'],
            name: $payload['name'],
            address: $payload['address'],
            city: $payload['city'],
            contact: $payload['contact'],
            remarks: $payload['remarks'],
            status: $payload['status'],
        );

        $result = $this->warehouseActions->create($dto);
        $this->assertDatabaseHas('warehouses', [
            'id' => $result->id,
            'company_id' => $payload['company_id'],
            'branch_id' => $payload['branch_id'],
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_warehouse_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(ArgumentCountError::class);
        $dto = new WarehouseCreateDTO(...[]);

        $this->warehouseActions->create($dto);
    }
}
