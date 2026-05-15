<?php

namespace Tests\Unit\Actions\WarehouseActions;

use App\Actions\Warehouse\WarehouseActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Tests\ActionsTestCase;

class WarehouseActionsReadTest extends ActionsTestCase
{
    private WarehouseActions $warehouseActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->warehouseActions = new WarehouseActions();
    }

    public function test_warehouse_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch()
                    ->has(Warehouse::factory()->count(2))
                )
            )
            ->create();

        $company = $user->companies()->first();

        $result = $this->warehouseActions->readAny(
            withTrashed: false,
            companyId: $company->id,
            branchId: null,
            search: '',
            status: null,
            includeId: null,
            execute: new ExecuteDTO(
                useCache: true,
                pagination: new ExecutePaginationDTO(
                    page: 1,
                    perPage: 10,
                ),
                get: null,
            ),
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_warehouse_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch()
                    ->has(Warehouse::factory()->count(2))
                )
            )
            ->create();

        $company = $user->companies()->first();

        $result = $this->warehouseActions->readAny(
            withTrashed: false,
            companyId: $company->id,
            branchId: null,
            search: '',
            status: null,
            includeId: null,
            execute: new ExecuteDTO(
                useCache: true,
                pagination: null,
                get: null,
            ),
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_warehouse_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch()
                    ->has(Warehouse::factory())
                )
            )
            ->create();

        $warehouse = Warehouse::first();

        $result = $this->warehouseActions->read($warehouse);

        $this->assertInstanceOf(Warehouse::class, $result);
    }
}
