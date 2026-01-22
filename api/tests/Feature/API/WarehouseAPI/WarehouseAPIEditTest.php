<?php

namespace Tests\Feature\API\WarehouseAPI;

use App\Enums\UserRolesEnum;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class WarehouseAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_warehouse_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $warehouse = Warehouse::factory()->for($company)->for($branch)->create();

        $payload = Warehouse::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.warehouse.edit', $warehouse->ulid), $payload);

        $api->assertUnauthorized();
    }

    public function test_warehouse_api_call_update_without_access_right_expect_forbidden_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $warehouse = Warehouse::factory()->for($company)->for($branch)->create();

        $payload = Warehouse::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.warehouse.edit', $warehouse->ulid), $payload);

        $api->assertForbidden();
    }

    public function test_warehouse_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_warehouse_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_warehouse_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $warehouse = Warehouse::factory()->for($company)->for($branch)->create();

        $payload = Warehouse::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.warehouse.edit', $warehouse->ulid), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
            'address' => $payload['address'],
            'city' => $payload['city'],
            'contact' => $payload['contact'],
            'remarks' => $payload['remarks'],
            'status' => $payload['status'],
        ]);
    }

    public function test_warehouse_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $warehouse = Warehouse::factory()->for($company)->for($branch)->create();

        $newBranchId = Branch::max('id') + 1;
        $payload = Warehouse::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($newBranchId),
        ])->toArray();

        $api = $this->json('POST', route('api.post.warehouse.edit', $warehouse->ulid), $payload);

        $api->assertUnprocessable();
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_warehouse_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
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

        $payload = Warehouse::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
            'code' => $warehouse_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.warehouse.edit', $warehouse_2->ulid), $payload);

        $api->assertUnprocessable();
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_warehouse_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
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

        $payload = Warehouse::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'branch_id' => Hashids::encode($branch_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.warehouse.edit', $warehouse_2->ulid), $payload);

        $api->assertSuccessful();
    }

    public function test_warehouse_api_call_update_with_sql_injection_payload_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $warehouse = Warehouse::factory()->for($company)->for($branch)->create();

        $payload = Warehouse::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
            'code' => "'; DROP TABLE warehouses; --",
            'name' => "'; DROP TABLE warehouses; --",
        ])->toArray();

        $api = $this->json('POST', route('api.post.warehouse.edit', $warehouse->ulid), $payload);

        $api->assertSuccessful();

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }
}
