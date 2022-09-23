<?php

namespace Tests\Feature\API;

use Exception;
use App\Models\Role;
use App\Models\User;
use App\Models\Branch;
use Tests\APITestCase;
use App\Models\Company;
use App\Enums\UserRoles;
use App\Models\Warehouse;
use Vinkla\Hashids\Facades\Hashids;
use Database\Seeders\WarehouseTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\Factories\Sequence;

class WarehouseAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /* #region store */
    public function test_warehouse_api_call_store_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory(), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        $branchId = $company->branches()->first()->id;

        $this->actingAs($user);

        $warehouseArr = Warehouse::factory()->make([
            'company_id' => Hashids::encode($companyId),
            'branch_id' => Hashids::encode($branchId),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.warehouse.save'), $warehouseArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('warehouses', [
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'code' => $warehouseArr['code'],
            'name' => $warehouseArr['name'],
            'address' => $warehouseArr['address'],
            'city' => $warehouseArr['city'],
            'contact' => $warehouseArr['contact'],
            'remarks' => $warehouseArr['remarks'],
            'status' => $warehouseArr['status'],
        ]);
    }

    public function test_warehouse_api_call_store_with_nonexistance_branch_id_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(3), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        $branchId = Branch::max('id') + 1;

        $this->actingAs($user);

        $warehouseArr = Warehouse::factory()->make([
            'company_id' => Hashids::encode($companyId),
            'branch_id' => Hashids::encode($branchId),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.warehouse.save'), $warehouseArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_warehouse_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory(), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        $branchId = $company->branches()->first()->id;

        $this->actingAs($user);

        Warehouse::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'code' => 'test1',
        ]);

        $warehouseArr = Warehouse::factory()->make([
            'company_id' => Hashids::encode($companyId),
            'branch_id' => Hashids::encode($branchId),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.warehouse.save'), $warehouseArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_warehouse_api_call_store_with_existing_code_in_different_company_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->count(2)->setIsDefault()
                            ->has(Branch::factory(), 'branches'), 'companies')
                    ->create();

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;
        $branchId_1 = $company_1->branches()->first()->id;

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;
        $branchId_2 = $company_2->branches()->first()->id;

        Warehouse::factory()->create([
            'company_id' => $companyId_1,
            'branch_id' => $branchId_1,
            'code' => 'test1',
        ]);

        $this->actingAs($user);

        $warehouseArr = Warehouse::factory()->make([
            'company_id' => Hashids::encode($companyId_2),
            'branch_id' => Hashids::encode($branchId_2),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.warehouse.save'), $warehouseArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('warehouses', [
            'company_id' => $companyId_2,
            'branch_id' => $branchId_2,
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

    public function test_warehouse_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory(), 'branches'), 'companies')
                    ->create();

        $this->actingAs($user);

        $warehouseArr = [];
        $api = $this->json('POST', route('api.post.db.company.warehouse.save'), $warehouseArr);

        $api->assertJsonValidationErrors(['company_id', 'code', 'name']);
    }
    /* #endregion */

    /* #region list */
    public function test_warehouse_api_call_list_with_or_without_pagination_expect_paginator_or_collection()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory(), 'branches'), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;
        $branchId = $company->branches()->first()->id;

        Warehouse::factory()->count(15)->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $api = $this->getJson(route('api.get.db.company.warehouse.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => '',
            'paginate' => true,
            'page' => 1,
            'perPage' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);

        $api = $this->getJson(route('api.get.db.company.warehouse.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => '',
            'paginate' => false,
            'page' => 1,
            'perPage' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
    }

    public function test_warehouse_api_call_list_with_search_expect_filtered_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory(), 'branches'), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;
        $branchId = $company->branches()->first()->id;

        $this->actingAs($user);

        Warehouse::factory()->insertStringInName('testing')->count(10)->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        Warehouse::factory()->count(10)->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $api = $this->getJson(route('api.get.db.company.warehouse.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => 'testing',
            'paginate' => true,
            'page' => 1,
            'perPage' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);

        $api->assertJsonFragment([
            'total' => 10,
        ]);
    }

    public function test_warehouse_api_call_list_without_search_querystring_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory(), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        $branchId = $company->branches()->first()->id;

        $this->actingAs($user);

        Warehouse::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'code' => 'test1',
        ]);

        $api = $this->getJson(route('api.get.db.company.warehouse.list', [
            'companyId' => Hashids::encode($companyId),
        ]));

        $api->assertStatus(422);
    }

    public function test_warehouse_api_call_list_with_special_char_in_search_expect_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory(), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        $branchId = $company->branches()->first()->id;

        $this->actingAs($user);

        Warehouse::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $api = $this->getJson(route('api.get.db.company.warehouse.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~",
            'paginate' => true,
            'page' => 1,
            'perPage' => 10,
            'refresh' => false,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }

    public function test_warehouse_api_call_list_with_negative_value_in_parameters_expect_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory(), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        $branchId = $company->branches()->first()->id;

        $this->actingAs($user);

        Warehouse::factory()->count(5)->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $api = $this->getJson(route('api.get.db.company.warehouse.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => '',
            'paginate' => true,
            'page' => -1,
            'perPage' => -10,
            'refresh' => false,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }
    /* #endregion */

    /* #region read */
    public function test_warehouse_api_call_read_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory(), 'branches'), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;
        $branch = $company->branches()->first();
        $branchId = $branch->id;

        $warehouse = Warehouse::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $uuid = $warehouse->uuid;

        $api = $this->getJson(route('api.get.db.company.warehouse.read', $uuid));

        $api->assertSuccessful();
    }

    public function test_warehouse_api_call_read_without_uuid_expect_exception()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory(), 'branches'), 'companies')
                    ->create();

        $this->actingAs($user);

        $this->getJson(route('api.get.db.company.warehouse.read', null));
    }

    public function test_warehouse_api_call_read_with_nonexistance_uuid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory(), 'branches'), 'companies')
                    ->create();

        $this->actingAs($user);

        $uuid = $this->faker->uuid();

        $api = $this->getJson(route('api.get.db.company.warehouse.read', $uuid));

        $api->assertStatus(404);
    }
    /* #endregion */

    /* #region update */
    public function test_warehouse_api_call_update_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory(), 'branches'), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;
        $branchId = $company->branches()->first()->id;

        Warehouse::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $warehouse = $company->warehouses()->inRandomOrder()->first();
        $warehouseArr = Warehouse::factory()->make([
            'company_id' => Hashids::encode($companyId),
            'branch_id' => Hashids::encode($branchId),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.warehouse.edit', $warehouse->uuid), $warehouseArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'company_id' => $companyId,
            'branch_id' => $branchId,
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
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory(), 'branches'), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;
        $branchId = $company->branches()->first()->id;

        Warehouse::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $newBranchId = Branch::max('id') + 1;
        $warehouse = $company->warehouses()->inRandomOrder()->first();
        $warehouseArr = Warehouse::factory()->make([
            'company_id' => Hashids::encode($companyId),
            'branch_id' => Hashids::encode($newBranchId),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.warehouse.edit', $warehouse->uuid), $warehouseArr);

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
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory(), 'branches'), 'companies')
                    ->create();

        $this->actingAs($user);
        
        $company = $user->companies->first();
        $companyId = $company->id;
        $branchId = $company->branches()->first()->id;

        Warehouse::factory()->count(5)->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $warehouses = $company->warehouses()->inRandomOrder()->take(2)->get();
        $warehouse_1 = $warehouses[0];
        $warehouse_2 = $warehouses[1];

        $warehouseArr = Warehouse::factory()->make([
            'company_id' => Hashids::encode($companyId),
            'branch_id' => Hashids::encode($branchId),
            'code' => $warehouse_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.warehouse.edit', $warehouse_2->uuid), $warehouseArr);

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
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory(), 'branches'), 'companies')
                    ->create();

        $company_1 = $user->companies->first();
        $companyId_1 = $company_1->id;
        $branchId_1 = $company_1->branches()->first()->id;
        
        $company_2 = $user->companies->first();
        $companyId_2 = $company_2->id;
        $branchId_2 = $company_2->branches()->first()->id;

        Warehouse::factory()->create([
            'company_id' => $companyId_1,
            'branch_id' => $branchId_1,
            'code' => 'test1',
        ]);

        Warehouse::factory()->create([
            'company_id' => $companyId_2,
            'branch_id' => $branchId_2,
            'code' => 'test2',
        ]);

        $this->actingAs($user);

        $warehouseArr = Warehouse::factory()->make([
            'company_id' => Hashids::encode($companyId_2),
            'branch_id' => Hashids::encode($branchId_2),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.warehouse.edit', $company_2->warehouses()->first()->uuid), $warehouseArr);

        $api->assertSuccessful();
    }
    /* #endregion */

    /* #region delete */
    public function test_warehouse_api_call_delete_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory(), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        $branch = $company->branches()->first();
        $branchId = $branch->id;

        Warehouse::factory()->create([
            'company_id' => $companyId,
            'branch_id' => $branchId,
        ]);

        $warehouse = $branch->warehouses()->inRandomOrder()->first();

        $this->actingAs($user);

        $api = $this->json('POST', route('api.post.db.company.warehouse.delete', $warehouse->uuid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('warehouses', [
            'id' => $warehouse->id,
        ]);
    }

    public function test_warehouse_api_call_delete_of_nonexistance_uuid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);

        $uuid = $this->faker->uuid();

        $api = $this->json('POST', route('api.post.db.company.warehouse.delete', $uuid));

        $api->assertStatus(404);
    }

    public function test_warehouse_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.db.company.warehouse.delete', null));

        $api->assertStatus(500);
    }
    /* #endregion */

    /* #region others */

    /* #endregion */
}