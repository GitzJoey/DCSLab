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

class WarehouseAPICreateTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_warehouse_api_call_store_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch()))
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $branch = $company->branches()->inRandomOrder()->first();

        $payload = Warehouse::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.warehouse.save'), $payload);

        $api->assertUnauthorized();
    }

    public function test_warehouse_api_call_store_without_access_right_expect_forbidden_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $branch = $company->branches()->inRandomOrder()->first();

        $payload = Warehouse::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.warehouse.save'), $payload);

        $api->assertForbidden();
    }

    public function test_warehouse_api_call_store_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_warehouse_api_call_store_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_warehouse_api_call_store_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $branch = $company->branches()->inRandomOrder()->first();

        $payload = Warehouse::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.warehouse.save'), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('warehouses', [
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

    public function test_warehouse_api_call_store_with_nonexistance_branch_id_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();

        $branchId = Branch::max('id') + 1;

        $payload = Warehouse::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branchId),
        ])->toArray();

        $api = $this->json('POST', route('api.post.warehouse.save'), $payload);

        $api->assertUnprocessable();
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_warehouse_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $branch = $company->branches()->inRandomOrder()->first();

        Warehouse::factory()->for($company)->for($branch)->create([
            'code' => 'test1',
        ]);

        $payload = Warehouse::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.warehouse.save'), $payload);

        $api->assertUnprocessable();
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_warehouse_api_call_store_with_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch()))
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()))
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->take(2)->get();

        $company_1 = $companies[0];
        $branch_1 = $company_1->branches()->inRandomOrder()->first();

        $company_2 = $companies[1];
        $branch_2 = $company_2->branches()->inRandomOrder()->first();

        Warehouse::factory()->for($company_1)->for($branch_1)->create([
            'code' => 'test1',
        ]);

        $payload = Warehouse::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'branch_id' => Hashids::encode($branch_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.warehouse.save'), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('warehouses', [
            'company_id' => $company_2->id,
            'branch_id' => $branch_2->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
            'address' => $payload['address'],
            'city' => $payload['city'],
            'contact' => $payload['contact'],
            'remarks' => $payload['remarks'],
            'status' => $payload['status'],
        ]);
    }

    public function test_warehouse_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch()))
            ->create();

        $this->actingAs($user);

        $payload = [];

        $api = $this->json('POST', route('api.post.warehouse.save'), $payload);

        $api->assertJsonValidationErrors(['company_id', 'code', 'name']);
    }

    public function test_warehouse_api_call_store_with_sql_injection_payload_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        $payload = Warehouse::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
            'code' => "'; DROP TABLE warehouses; --",
            'name' => "'; DROP TABLE warehouses; --",
        ])->toArray();

        $api = $this->json('POST', route('api.post.warehouse.save'), $payload);

        // Should succeed because it's just text, but shouldn't execute SQL.
        // If it was vulnerable, the table might be dropped or error out.
        // We expect it to be saved as is or handled gracefully.
        // Here we just check it is successful (saved as text) or validation error if there are rules against special chars.
        // Assuming no strict regex on code/name, it should be saved.
        $api->assertSuccessful();

        $this->assertDatabaseHas('warehouses', [
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }
}
