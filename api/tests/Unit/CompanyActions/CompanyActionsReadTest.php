<?php

namespace Tests\Feature;

use App\Actions\Company\CompanyActions;
use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\ActionsTestCase;

class CompanyActionsReadTest extends ActionsTestCase
{
    use WithFaker;

    private CompanyActions $companyActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyActions = new CompanyActions();
    }

    public function test_company_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                    )->create();

        $result = $this->companyActions->readAny(
            userId: $user->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_company_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                    )->create();

        $result = $this->companyActions->readAny(
            userId: $user->id,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_company_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setStatusActive()->setIsDefault())
                    ->has(Company::factory()->setStatusActive()->count(3))
                    ->has(Company::factory()->setStatusActive()->insertStringInName('testing')->count(2))
                ->create();

        $result = $this->companyActions->readAny(
            userId: $user->id,
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 2);
    }

    public function test_company_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setStatusActive()->setIsDefault())
                    ->has(Company::factory()->setStatusActive()->count(4))
                    ->create();

        $result = $this->companyActions->readAny(
            userId: $user->id,
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 5);
    }

    public function test_company_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setStatusActive()->setIsDefault())
                    ->has(Company::factory()->setStatusActive()->count(4))
                    ->create();

        $result = $this->companyActions->readAny(
            userId: $user->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 5);
    }

    public function test_company_actions_call_read_expect_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                    )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->companyActions->read($company);

        $this->assertInstanceOf(Company::class, $result);
    }
}
