<?php

namespace Tests\Feature;

use App\Actions\Warehouse\WarehouseActions;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WarehouseActionsDeleteTest extends TestCase
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

    public function test_warehouse_actions_call_delete_expect_bool()
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

        $result = $this->warehouseActions->delete($warehouse);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('warehouses', [
            'id' => $warehouse->id,
        ]);
    }
}
