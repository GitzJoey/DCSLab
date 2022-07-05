<?php

namespace Tests\Feature\Service;

use Exception;
use App\Models\User;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Warehouse;
use Tests\ServiceTestCase;
use App\Actions\RandomGenerator;
use App\Services\WarehouseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;

class WarehouseServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->warehouseService = app(WarehouseService::class);
    }

    #region create
    public function test_warehouse_service_call_create_expect_db_has_record()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(20), 'branches'), 'companies')
                    ->create();
        
        $warehouseArr = Warehouse::factory()->make([
            'company_id' => $user->companies->first()->id,
            'branch_id' => $user->companies()->has('branches')->first()->branches()->first()->id
        ]);

        $result = $this->warehouseService->create($warehouseArr->toArray());

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

    public function test_warehouse_service_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->warehouseService->create([]);
    }

    #endregion

    #region list

    public function test_warehouse_service_call_list_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $result = $this->warehouseService->list(
            companyId: $user->companies->first()->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_warehouse_service_call_list_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $result = $this->warehouseService->list(
            companyId: $user->companies->first()->id,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_warehouse_service_call_list_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;
        $result = $this->warehouseService->list(
            companyId: $maxId,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_warehouse_service_call_list_with_search_parameter_expect_filtered_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(20), 'branches'), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        $branchId = $user->companies()->has('branches')->first()->branches()->first()->id;

        Warehouse::factory()->count(10)->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'name' => $this->faker->name().' '.'testing'
        ]);

        Warehouse::factory()->count(10)->create([
            'company_id' => $companyId,
            'branch_id' => $branchId
        ]);

        $result = $this->warehouseService->list(
            companyId: $companyId, 
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 10);
    }

    public function test_warehouse_service_call_list_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(20), 'branches'), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        $branchId = $user->companies()->has('branches')->first()->branches()->first()->id;

        Warehouse::factory()->count(25)->create([
            'company_id' => $companyId,
            'branch_id' => $branchId
        ]);

        $result = $this->warehouseService->list(
            companyId: $companyId, 
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() > 1);
    }

    public function test_warehouse_service_call_list_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(20), 'branches'), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        $branchId = $user->companies()->has('branches')->first()->branches()->first()->id;

        Warehouse::factory()->count(25)->create([
            'company_id' => $companyId,
            'branch_id' => $branchId
        ]);

        $result = $this->warehouseService->list(
            companyId: $companyId, 
            search: '',
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() > 1);
    }

    #endregion

    #region read

    public function test_warehouse_service_call_read_expect_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Branch::factory()->setIsMainBranch(), 'branches'), 'companies')
                    ->create();

        $companyId = $user->companies()->has('branches')->inRandomOrder()->first()->id;
        $branchId = Branch::where('company_id', '=', $companyId)->inRandomOrder()->first()->id;
        Warehouse::factory()->count(25)->create([
            'company_id' => $companyId,
            'branch_id' => $branchId
        ]);
        
        $warehouse = $user->companies->first()->branches()->inRandomOrder()->first()->warehouses()->inRandomOrder()->first();

        $result = $this->warehouseService->read($warehouse);

        $this->assertInstanceOf(Warehouse::class, $result);
    }

    #endregion

    #region update

    public function test_warehouse_service_call_update_expect_db_updated()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Branch::factory()->setIsMainBranch(), 'branches'), 'companies')
                    ->create();

        $companyId = $user->companies()->has('branches')->inRandomOrder()->first()->id;
        $branchId = Branch::where('company_id', '=', $companyId)->inRandomOrder()->first()->id;
        Warehouse::factory()->count(25)->create([
            'company_id' => $companyId,
            'branch_id' => $branchId
        ]);
        
        $warehouse = $user->companies->first()->branches()->inRandomOrder()->first()->warehouses()->inRandomOrder()->first();
        $warehouseArr = Warehouse::factory()->make();
        $result = $this->warehouseService->update($warehouse, $warehouseArr->toArray());

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

    public function test_warehouse_service_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Branch::factory()->setIsMainBranch(), 'branches'), 'companies')
                    ->create();

        $companyId = $user->companies()->has('branches')->inRandomOrder()->first()->id;
        $branchId = Branch::where('company_id', '=', $companyId)->inRandomOrder()->first()->id;
        Warehouse::factory()->count(25)->create([
            'company_id' => $companyId,
            'branch_id' => $branchId
        ]);
        
        $warehouse = $user->companies->first()->branches()->inRandomOrder()->first()->warehouses()->inRandomOrder()->first();
        $warehouseArr = [];
            
        $this->warehouseService->update($warehouse, $warehouseArr);
    }

    #endregion

    #region delete

    public function test_warehouse_service_call_delete_expect_bool()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Branch::factory()->setIsMainBranch(), 'branches'), 'companies')
                    ->create();

        $companyId = $user->companies()->has('branches')->inRandomOrder()->first()->id;
        $branchId = Branch::where('company_id', '=', $companyId)->inRandomOrder()->first()->id;
        Warehouse::factory()->count(25)->create([
            'company_id' => $companyId,
            'branch_id' => $branchId
        ]);
        
        $warehouse = $user->companies->first()->branches()->inRandomOrder()->first()->warehouses()->inRandomOrder()->first();
            
        $result = $this->warehouseService->delete($warehouse);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('warehouses', [
            'id' => $warehouse->id
        ]);
    }

    #endregion

    #region others

    

    #endregion
}
