<?php

namespace Tests\Feature;

use App\Actions\Warehouse\WarehouseActions;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WarehouseActionsReadTest extends TestCase
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

    public function test_warehouse_actions_call_read_expect_object()
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

        $result = $this->warehouseActions->read($warehouse);

        $this->assertInstanceOf(Warehouse::class, $result);
    }
}
