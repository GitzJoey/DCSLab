<?php

namespace Tests\Feature\Service;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Services\BranchService;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\ServiceTestCase;

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
            'company_id' => $user->companies->first()->id,
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
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        Branch::factory()->count(10)->create([
            'company_id' => $companyId,
            'name' => 'Kantor Cabang '.$this->faker->randomElement(['Utama', 'Pembantu', 'Daerah']).' '.'testing',
        ]);

        Branch::factory()->count(10)->create([
            'company_id' => $companyId,
        ]);

        $result = $this->branchService->list(
            companyId: $companyId,
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 10);
    }

    public function test_branch_service_call_list_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        Branch::factory()->count(25)->create([
            'company_id' => $companyId,
        ]);

        $result = $this->branchService->list(
            companyId: $companyId,
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 25);
    }

    public function test_branch_service_call_list_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        Branch::factory()->count(25)->create([
            'company_id' => $companyId,
        ]);

        $result = $this->branchService->list(
            companyId: $companyId,
            search: '',
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 25);
    }

    #endregion

    #region read

    public function test_branch_service_call_read_expect_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Branch::factory()->count(20), 'branches'), 'companies')
                    ->create();

        $branch = $user->companies->first()->branches()->inRandomOrder()->first();

        $result = $this->branchService->read($branch);

        $this->assertInstanceOf(Branch::class, $result);
    }

    #endregion

    #region update

    public function test_branch_service_call_update_expect_db_updated()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Branch::factory()->setIsMainBranch(), 'branches'), 'companies')
                    ->create();

        $branch = $user->companies->first()->branches->first();
        $branchArr = Branch::factory()->make();

        $result = $this->branchService->update($branch, $branchArr->toArray());

        $this->assertInstanceOf(Branch::class, $result);
        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'company_id' => $branch->company_id,
            'code' => $branchArr['code'],
            'name' => $branchArr['name'],
        ]);
    }

    public function test_branch_service_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Branch::factory()->setIsMainBranch(), 'branches'), 'companies')
                    ->create();

        $branch = $user->companies->first()->branches->first();
        $branchArr = [];

        $this->branchService->update($branch, $branchArr);
    }

    #endregion

    #region delete

    public function test_branch_service_call_delete_expect_bool()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $branch = $user->companies->first()->branches->first();

        $result = $this->branchService->delete($branch);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('branches', [
            'id' => $branch->id,
        ]);
    }

    #endregion

    #region others

    public function test_branch_service_call_function_getBranchByCompany_expect_collection_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $result = $this->branchService->getBranchByCompany(companyId: $companyId);

        $this->assertInstanceOf(Collection::class, $result);

        $result = $this->branchService->getBranchByCompany(company: $company);

        $this->assertInstanceOf(Collection::class, $result);

        $result = $this->branchService->getBranchByCompany(companyId: $companyId, company: $company);

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_branch_service_call_function_getMainBranchByCompany_expect_main_branch_returned()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        Branch::factory()->setIsMainBranch()->create(['company_id' => $companyId]);
        Branch::factory()->count(5)->create(['company_id' => $companyId]);

        $result = $this->branchService->getMainBranchByCompany(companyId: $companyId);

        $this->assertInstanceOf(Branch::class, $result);
        $this->assertTrue(boolval($result->is_main));

        $result = $this->branchService->getMainBranchByCompany(company: $company);

        $this->assertInstanceOf(Branch::class, $result);
        $this->assertTrue(boolval($result->is_main));

        $result = $this->branchService->getMainBranchByCompany(companyId: $companyId, company: $company);

        $this->assertInstanceOf(Branch::class, $result);
        $this->assertTrue(boolval($result->is_main));
    }

    public function test_branch_service_call_function_resetMainBranch_expect_no_main_branch_exists()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        Branch::factory()->setIsMainBranch()->create(['company_id' => $companyId]);
        Branch::factory()->count(5)->create(['company_id' => $companyId]);

        $result = $this->branchService->resetMainBranch(companyId: $companyId);

        $this->assertTrue($result);
        $this->assertTrue(Branch::whereCompanyId($companyId)->where('is_main', '=', true)->count() == 0);

        Branch::whereCompanyId($companyId)->inRandomOrder()->first()->update(['is_main' => true]);

        $result = $this->branchService->resetMainBranch(company: $company);

        $this->assertTrue($result);
        $this->assertTrue(Branch::whereCompanyId($companyId)->where('is_main', '=', true)->count() == 0);

        Branch::whereCompanyId($companyId)->inRandomOrder()->first()->update(['is_main' => true]);

        $result = $this->branchService->resetMainBranch(companyId: $companyId, company: $company);

        $this->assertTrue($result);
        $this->assertTrue(Branch::whereCompanyId($companyId)->where('is_main', '=', true)->count() == 0);
    }

    public function test_branch_service_call_function_generateUniqueCode_expect_unique_code_returned()
    {
        $this->assertIsString($this->branchService->generateUniqueCode());
    }

    public function test_branch_service_call_function_isUniqueCode_expect_can_detect_unique_code()
    {
        $user = User::factory()
                    ->has(Company::factory()->count(2)->state(new Sequence(['default' => true], ['default' => false])), 'companies')
                    ->create();

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;

        Branch::factory()->create([
            'company_id' => $companyId_1,
            'code' => 'test1',
        ]);

        Branch::factory()->create([
            'company_id' => $companyId_2,
            'code' => 'test2',
        ]);

        $this->assertFalse($this->branchService->isUniqueCode('test1', $companyId_1));
        $this->assertTrue($this->branchService->isUniqueCode('test2', $companyId_1));
        $this->assertTrue($this->branchService->isUniqueCode('test3', $companyId_1));
        $this->assertTrue($this->branchService->isUniqueCode('test1', $companyId_2));
    }

    #endregion
}
