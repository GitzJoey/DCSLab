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

        dd($api);

        $api->assertSuccessful();
        $this->assertDatabaseHas('branches', [
            'company_id' => $companyId,
            'code' => $branchArr['code'], 
            'name' => $branchArr['name'],
        ]);
    }

    public function test_branch_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_api_call_store_with_existing_code_in_different_company_expect_successful()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_api_call_store_with_empty_string_parameters_expect_failed()
    {
        $this->markTestSkipped('Under Constructions');
    }

    #endregion

    #region list

    public function test_branch_api_call_list_with_or_without_pagination_expect_paginator_or_collection()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_api_call_list_with_empty_search_expect_results()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_api_call_list_without_search_querystring_expect_failed()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_api_call_list_with_special_char_in_search_expect_results()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_api_call_list_with_negative_value_in_parameters_expect_results()
    {
        $this->markTestSkipped('Under Constructions');
    }

    #endregion

    #region read

    public function test_branch_api_call_read_expect_successful()
    {

    }

    public function test_branch_api_call_read_without_uuid_expect_failed()
    {

    }

    public function test_branch_api_call_read_with_nonexistance_uuid_expect_failed()
    {
        
    }

    #endregion

    #region update

    public function test_branch_api_call_update_expect_successful()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $this->markTestSkipped('Under Constructions');
    }

    #endregion

    #region delete

    public function test_branch_api_call_delete_expect_successful()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_api_call_delete_of_nonexistance_uuid_expect_failed()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_api_call_delete_without_parameters_expect_failed()
    {
        $this->markTestSkipped('Under Constructions');
    }

    #endregion

    #region others

    #endregion
}