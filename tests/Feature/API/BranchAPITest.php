<?php

namespace Tests\Feature\API;

use App\Models\Branch;
use Tests\APITestCase;
use App\Enums\ActiveStatus;
use App\Actions\RandomGenerator;
use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BranchAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        Parent::setUp();
    }

    #region store

    public function test_branch_api_call_store_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();
        
        $company = $user->companies->first();
        $companyId = $company->id;
        
        $this->actingAs($user);

        $branchArr = array_merge([
            'company_id' => Hashids::encode($companyId)
        ], Branch::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.company.branch.save'), $branchArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('branches', [
            'company_id' => $companyId,
            'code' => $branchArr['code'], 
            'name' => $branchArr['name'],
        ]);
    }

    public function test_branch_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
            
        Branch::factory()->create([
            'company_id' => $companyId,
            'code' => 'test1'
        ]);

        $this->actingAs($user);

        $branchArr = array_merge([
            'company_id' => Hashids::encode($companyId)
        ], Branch::factory()->make([
            'code' => 'test1'
        ])->toArray());

        $api = $this->json('POST', route('api.post.db.company.branch.save'), $branchArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors'
        ]);
    }

    public function test_branch_api_call_store_with_existing_code_in_different_company_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->count(2), 'companies')
                    ->create();

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;

        Branch::factory()->create([
            'company_id' => $companyId_1,
            'code' => 'test1'
        ]);

        $this->actingAs($user);

        $branchArr = array_merge([
            'company_id' => Hashids::encode($companyId_2)
        ], Branch::factory()->make([
            'code' => 'test1'
        ])->toArray());

        $api = $this->json('POST', route('api.post.db.company.branch.save'), $branchArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('branches', [
            'company_id' => $companyId_2,
            'code' => $branchArr['code']
        ]);
    }

    public function test_branch_api_call_store_with_empty_string_parameters_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                ->has(Company::factory()->setIsDefault(), 'companies')
                ->create();

        $this->actingAs($user);

        $branchArr = [];
        $api = $this->json('POST', route('api.post.db.company.branch.save'), $branchArr);
        
        $api->assertStatus(500);
    }

    #endregion

    #region list

    public function test_branch_api_call_list_with_or_without_pagination_expect_paginator_or_collection()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(15), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.branch.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => '',
            'paginate' => true,
            'page' => 1,
            'perPage' => 10,
            'refresh' => true
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data', 
            'links' => [
                'first', 'last', 'prev', 'next'
            ], 
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'
            ]
        ]);

        $api = $this->getJson(route('api.get.db.company.branch.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => '',
            'paginate' => false,
            'page' => 1,
            'perPage' => 10,
            'refresh' => true
        ]));

        $api->assertSuccessful();
    }

    public function test_branch_api_call_list_with_search_expect_filtered_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        Branch::factory()->count(10)->create([
            'company_id' => $companyId,
            'name' => 'Kantor Cabang '.$this->faker->randomElement(['Utama','Pembantu','Daerah']).' '.'testing'
        ]);

        Branch::factory()->count(10)->create([
            'company_id' => $companyId,
        ]);

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.branch.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => 'testing',
            'paginate' => true,
            'page' => 1,
            'perPage' => 10,
            'refresh' => true
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data', 
            'links' => [
                'first', 'last', 'prev', 'next'
            ], 
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'
            ]
        ]);

        $api->assertJsonFragment([
            'total' => 10
        ]);
    }

    public function test_branch_api_call_list_without_search_querystring_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(2), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        
        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.branch.list', [
            'companyId' => Hashids::encode($companyId),
        ]));

        $api->assertStatus(422);
    }

    public function test_branch_api_call_list_with_special_char_in_search_expect_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        
        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.branch.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~",
            'paginate' => true,
            'page' => 1,
            'perPage' => 10,
            'refresh' => false
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data', 
            'links' => [
                'first', 'last', 'prev', 'next'
            ], 
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'
            ]
        ]);
    }

    public function test_branch_api_call_list_with_negative_value_in_parameters_expect_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        
        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.branch.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => '',
            'paginate' => true,
            'page' => -1,
            'perPage' => -10,
            'refresh' => false
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data', 
            'links' => [
                'first', 'last', 'prev', 'next'
            ], 
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'
            ]
        ]);        
    }

    #endregion

    #region read

    public function test_branch_api_call_read_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        
        $this->actingAs($user);

        $uuid = $company->branches()->inRandomOrder()->first()->uuid;

        $api = $this->getJson(route('api.get.db.company.branch.read', $uuid));

        $api->assertSuccessful();
    }

    public function test_branch_api_call_read_without_uuid_expect_exception()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        
        $this->actingAs($user);

        $this->getJson(route('api.get.db.company.branch.read', null));
    }

    public function test_branch_api_call_read_with_nonexistance_uuid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        
        $this->actingAs($user);

        $uuid = $this->faker->uuid();

        $api = $this->getJson(route('api.get.db.company.branch.read', $uuid));

        $api->assertStatus(404);
    }

    #endregion

    #region update

    public function test_branch_api_call_update_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        
        $this->actingAs($user);

        $branch = $company->branches()->inRandomOrder()->first();
        $branchArr = array_merge([
            'company_id' => Hashids::encode($companyId)
        ], Branch::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.company.branch.edit', $branch->uuid), $branchArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'company_id' => $companyId,
            'code' => $branchArr['code'], 
            'name' => $branchArr['name']
        ]);
    }

    public function test_branch_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        
        $this->actingAs($user);
        
        $branches = $company->branches()->inRandomOrder()->take(2)->get();
        $branch_1 = $branches[0];
        $branch_2 = $branches[1];

        $branchArr = array_merge([
            'company_id' => Hashids::encode($companyId)
        ], Branch::factory()->make([
            'code' => $branch_1->code
        ])->toArray());

        $api = $this->json('POST', route('api.post.db.company.branch.edit', $branch_2->uuid), $branchArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_branch_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->count(2)->state(new Sequence(['default' => true], ['default' => false])), 'companies')
                    ->create();

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;

        Branch::factory()->create([
            'company_id' => $companyId_1,
            'code' => 'test1'
        ]);

        Branch::factory()->create([
            'company_id' => $companyId_2,
            'code' => 'test2'
        ]);

        $this->actingAs($user);

        $branchArr = array_merge([
            'company_id' => Hashids::encode($companyId_2)
        ], Branch::factory()->make([
            'code' => 'test1'
        ])->toArray());

        $api = $this->json('POST', route('api.post.db.company.branch.edit', $company_2->branches()->first()->uuid), $branchArr);

        $api->assertSuccessful();
    }

    #endregion

    #region delete

    public function test_branch_api_call_delete_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $branch = $company->branches()->inRandomOrder()->first();
        
        $this->actingAs($user);
        
        $api = $this->json('POST', route('api.post.db.company.branch.delete', $branch->uuid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('branches', [
            'id' => $branch->id
        ]);
    }

    public function test_branch_api_call_delete_of_nonexistance_uuid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $uuid = $this->faker->uuid();

        $api = $this->json('POST', route('api.post.db.company.branch.delete', $uuid));
        
        $api->assertStatus(404);
    }

    public function test_branch_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.db.company.branch.delete', null));

        dd($api);
    }

    #endregion

    #region others

    #endregion
}