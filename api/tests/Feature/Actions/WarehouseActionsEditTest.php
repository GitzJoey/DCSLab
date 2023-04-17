<?php

namespace Tests\Feature;

use App\Actions\Warehouse\WarehouseActions;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\Warehouse;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WarehouseActionsEditTest extends TestCase
{
    use WithFaker;

    private $warehouseActions;

    private $companySeeder;

    private $warehouseSeeder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->warehouseActions = app(WarehouseActions::class);
    }

    public function test_warehouse_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Branch::factory()->setIsMainBranch(), 'branches'), 'companies')
                    ->create();

        $companyId = $user->companies()->has('branches')->inRandomOrder()->first()->id;
        $branchId = Branch::where('company_id', '=', $companyId)->inRandomOrder()->first()->id;
        Warehouse::factory()->count(25)->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $warehouse = $user->companies->first()->branches()->inRandomOrder()->first()->warehouses()->inRandomOrder()->first();
        $warehouseArr = Warehouse::factory()->make();
        $result = $this->warehouseActions->update($warehouse, $warehouseArr->toArray());

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
                    ->has(Company::factory()->setIsDefault()
                        ->has(Branch::factory()->setIsMainBranch(), 'branches'), 'companies')
                    ->create();

        $companyId = $user->companies()->has('branches')->inRandomOrder()->first()->id;
        $branchId = Branch::where('company_id', '=', $companyId)->inRandomOrder()->first()->id;
        Warehouse::factory()->count(25)->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $warehouse = $user->companies->first()->branches()->inRandomOrder()->first()->warehouses()->inRandomOrder()->first();
        $warehouseArr = [];

        $this->warehouseActions->update($warehouse, $warehouseArr);
    }
}
