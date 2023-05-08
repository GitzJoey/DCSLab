<?php

namespace Tests\Feature\API;

use App\Enums\UserRoles;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Vinkla\Hashids\Facades\Hashids;

class WarehouseAPIEditTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_warehouse_api_call_update_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $warehouse = Warehouse::factory()->for($company)->for($branch)->create();

        $warehouseArr = Warehouse::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.warehouse.edit', $warehouse->ulid), $warehouseArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'code' => $warehouseArr['code'],
            'name' => $warehouseArr['name'],
            'name' => $warehouseArr['name'],
            'address' => $warehouseArr['address'],
            'city' => $warehouseArr['city'],
            'contact' => $warehouseArr['contact'],
            'remarks' => $warehouseArr['remarks'],
            'status' => $warehouseArr['status'],
        ]);
    }

    public function test_warehouse_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $warehouse = Warehouse::factory()->for($company)->for($branch)->create();

        $newBranchId = Branch::max('id') + 1;
        $warehouseArr = Warehouse::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($newBranchId),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.warehouse.edit', $warehouse->ulid), $warehouseArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_warehouse_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    )->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $branch = $company->branches()->inRandomOrder()->first();
        Warehouse::factory()->for($company)->for($branch)->count(2)->create();

        $warehouses = $company->warehouses()->inRandomOrder()->take(2)->get();
        $warehouse_1 = $warehouses[0];
        $warehouse_2 = $warehouses[1];

        $warehouseArr = Warehouse::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
            'code' => $warehouse_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.warehouse.edit', $warehouse_2->ulid), $warehouseArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_warehouse_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Branch::factory()->setStatusActive()->setIsMainBranch()))
                    ->has(Company::factory()->setStatusActive()
                        ->has(Branch::factory()->setStatusActive()))
                    ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        $branch_1 = $company_1->branches()->first();
        Warehouse::factory()->for($company_1)->for($branch_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $branch_2 = $company_2->branches()->first();
        $warehouse_2 = Warehouse::factory()->for($company_2)->for($branch_2)->create([
            'code' => 'test2',
        ]);

        $warehouseArr = Warehouse::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'branch_id' => Hashids::encode($branch_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.warehouse.edit', $warehouse_2->ulid), $warehouseArr);

        $api->assertSuccessful();
    }
}
