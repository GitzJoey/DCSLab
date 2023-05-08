<?php

namespace Tests\Feature;

use App\Actions\Branch\BranchActions;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BranchActionsReadTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branchActions = new BranchActions();
    }

    public function test_branch_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
                ->has(Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->branchActions->readAny(
            companyId: $company->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_branch_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
                ->has(Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->branchActions->readAny(
            companyId: $company->id,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_branch_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;
        $result = $this->branchActions->readAny(
            companyId: $maxId,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_branch_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $user = User::factory()
                ->has(Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    ->has(Branch::factory()->setStatusActive()->count(2))
                    ->has(Branch::factory()->setStatusActive()->insertStringInName('testing')->count(3))
                )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->branchActions->readAny(
            companyId: $company->id,
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 3);
    }

    public function test_branch_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
                ->has(Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    ->has(Branch::factory()->setStatusActive()->count(2))
                )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->branchActions->readAny(
            companyId: $company->id,
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 3);
    }

    public function test_branch_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
                ->has(Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    ->has(Branch::factory()->setStatusActive()->count(2))
                )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->branchActions->readAny(
            companyId: $company->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 3);
    }

    public function test_branch_actions_call_read_expect_object()
    {
        $user = User::factory()
                ->has(Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                )->create();

        $branch = $user->companies()->inRandomOrder()->first()
                    ->branches()->inRandomOrder()->first();

        $result = $this->branchActions->read($branch);

        $this->assertInstanceOf(Branch::class, $result);
    }
}
