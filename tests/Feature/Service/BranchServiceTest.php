<?php

namespace Tests\Feature\Service;

use App\Models\Branch;
use App\Models\Company;
use Tests\ServiceTestCase;
use App\Services\BranchService;
use App\Actions\RandomGenerator;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;

class BranchServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branchService = app(BranchService::class);
    }

    #region create
    public function test_branch_service_call_create_expect_object()
    {
        $dev_role = Role::where('name', '=', 'developer')->first();
        $user = User::factory()
                    ->hasAttached($dev_role)
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $branchArr = Branch::factory()->make([
            'company_id' => $user->companies->first()->id
        ]);

        $result = $this->branchService->create($branchArr->toArray());

        $this->assertDatabaseHas('branches', $branchArr->toArray());
    }

    public function test_branch_service_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->markTestSkipped('Under Constructions');
    }

    #endregion

    #region list

    public function test_branch_service_call_list_with_paginate_true_expect_paginator_object()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_service_call_list_with_paginate_false_expect_collection_object()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_service_call_list_without_search_parameter_expect_results()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_service_call_list_with_search_parameter_expect_filtered_results()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_service_call_list_with_page_parameter_negative_expect_results()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_service_call_list_with_perpage_parameter_negative_expect_results()
    {
        $this->markTestSkipped('Under Constructions');
    }

    #endregion

    #region read

    public function test_branch_service_call_read_expect_object()
    {
        $this->markTestSkipped('Under Constructions');
    }

    #endregion

    #region update

    public function test_branch_service_call_update_expect_object()
    {
        $this->markTestSkipped('Under Constructions');
    }

    public function test_branch_service_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->markTestSkipped('Under Constructions');
    }

    #endregion

    #region delete

    public function test_branch_service_call_delete_expect_bool()
    {
        $this->markTestSkipped('Under Constructions');
    }

    #endregion

    #region others

    

    #endregion
}
