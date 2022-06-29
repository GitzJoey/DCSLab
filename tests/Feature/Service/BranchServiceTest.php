<?php

namespace Tests\Feature\Service;

use App\Models\Branch;
use App\Models\Company;
use Tests\ServiceTestCase;
use App\Services\BranchService;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

class BranchServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branchService = app(BranchService::class);
    }

    #region create
    public function test_branch_service_call_create_expect_db_has_record()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $branchArr = Branch::factory()->make([
            'company_id' => $user->companies->first()->id
        ]);

        $result = $this->branchService->create($branchArr->toArray());

        $this->assertDatabaseHas('branches', [
            'id' => $result->id,
            'company_id' => $branchArr['company_id'],
            'code' => $branchArr['code'],
            'name' => $branchArr['name'],
        ]);
    }

    public function test_branch_service_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->branchService->create([]);
    }

    #endregion

    #region list

    public function test_branch_service_call_list_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(20), 'branches'), 'companies')
                    ->create();

        $result = $this->branchService->list(
            companyId: $user->companies->first()->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_branch_service_call_list_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(20), 'branches'), 'companies')
                    ->create();

        $result = $this->branchService->list(
            companyId: $user->companies->first()->id,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_branch_service_call_list_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;
        $result = $this->branchService->list(companyId: $maxId, search: '', paginate: false);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_branch_service_call_list_with_search_parameter_expect_filtered_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Branch::factory()->count(20), 'branches'), 'companies')
                    ->create();

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

    public function test_branch_service_call_update_expect_db_updated()
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
