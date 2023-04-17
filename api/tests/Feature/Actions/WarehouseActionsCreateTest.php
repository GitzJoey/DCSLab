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

class WarehouseActionsCreateTest extends TestCase
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

    public function test_warehouse_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(20), 'branches'), 'companies')
                    ->create();

        $warehouseArr = Warehouse::factory()->make([
            'company_id' => $user->companies->first()->id,
            'branch_id' => $user->companies()->has('branches')->first()->branches()->first()->id,
        ]);

        $result = $this->warehouseActions->create($warehouseArr->toArray());

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
