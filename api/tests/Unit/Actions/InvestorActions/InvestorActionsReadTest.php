<?php

namespace Tests\Unit\Actions\InvestorActions;

use App\Actions\Investor\InvestorActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Models\Company;
use App\Models\Investor;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\ActionsTestCase;

class InvestorActionsReadTest extends ActionsTestCase
{
    private InvestorActions $investorActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->investorActions = new InvestorActions();
    }

    public function test_investor_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Investor::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->investorActions->readAny(
            withTrashed: false,
            search: '',
            companyId: $company->id,
            includeId: null,
            execute: new ExecuteDTO(
                useCache: true,
                pagination: new ExecutePaginationDTO(
                    page: 1,
                    perPage: 10,
                ),
                get: null,
            )
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_investor_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Investor::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->investorActions->readAny(
            withTrashed: false,
            search: '',
            companyId: $company->id,
            includeId: null,
            execute: new ExecuteDTO(
                useCache: true,
                pagination: null,
                get: new ExecuteGetDTO(
                    limit: 10,
                ),
            )
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_investor_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $result = $this->investorActions->readAny(
            withTrashed: false,
            search: '',
            companyId: $maxId,
            includeId: null,
            execute: new ExecuteDTO(
                useCache: true,
                pagination: null,
                get: new ExecuteGetDTO(
                    limit: 10,
                ),
            )
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_investor_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $investorCount = 4;
        $idxTest = random_int(0, $investorCount - 1);
        $defaultName = Investor::factory()->make()->name;
        $testname = Investor::factory()->insertStringInName('testing')->make()->name;

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Investor::factory()->count($investorCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'name' => $sequence->index == $idxTest ? $testname : $defaultName,
                        ]
                    ))
                )
            )
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->investorActions->readAny(
            withTrashed: false,
            search: 'testing',
            companyId: $company->id,
            includeId: null,
            execute: new ExecuteDTO(
                useCache: true,
                pagination: new ExecutePaginationDTO(
                    page: 1,
                    perPage: 10,
                ),
                get: null,
            )
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 1);
    }

    public function test_investor_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_investor_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_investor_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Investor::factory())
            )->create();

        $investor = $user->companies()->inRandomOrder()->first()
            ->investors()->inRandomOrder()->first();

        $result = $this->investorActions->read($investor);

        $this->assertInstanceOf(Investor::class, $result);
    }
}
