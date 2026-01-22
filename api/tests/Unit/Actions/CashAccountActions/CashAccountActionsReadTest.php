<?php

namespace Tests\Unit\Actions\CashAccountActions;

use App\Actions\CashAccount\CashAccountActions;
use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\ActionsTestCase;

class CashAccountActionsReadTest extends ActionsTestCase
{
    private CashAccountActions $cashAccountActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashAccountActions = new CashAccountActions();
    }

    public function test_cash_account_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    CashAccount::factory()->state(function (array $attributes, Company $company) {
                        return [
                            'branch_id' => $company->branches()->inRandomOrder()->first()->id,
                        ];
                    })
                )
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->cashAccountActions->readAny(
            companyId: $company->id,
            useCache: true,
            withTrashed: false,

            search: '',

            paginate: true,
            page: 1,
            perPage: 10,
            limit: null
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_cash_account_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    CashAccount::factory()->state(function (array $attributes, Company $company) {
                        return [
                            'branch_id' => $company->branches()->inRandomOrder()->first()->id,
                        ];
                    })
                )
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->cashAccountActions->readAny(
            companyId: $company->id,
            useCache: true,
            withTrashed: false,

            search: '',

            paginate: false,
            page: null,
            perPage: null,
            limit: 10
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_cash_account_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $result = $this->cashAccountActions->readAny(
            companyId: $maxId,
            useCache: true,
            withTrashed: false,

            search: '',

            paginate: false,
            page: null,
            perPage: null,
            limit: 10
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_cash_account_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $cashAccountCount = 4;
        $idxTest = random_int(0, $cashAccountCount - 1);
        $defaultName = CashAccount::factory()->make()->name;
        $testname = CashAccount::factory()->insertStringInName('testing')->make()->name;

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    CashAccount::factory()->count($cashAccountCount)
                        ->state(function (array $attributes, Company $company) {
                            return [
                                'branch_id' => $company->branches()->inRandomOrder()->first()->id,
                            ];
                        })
                        ->state(new Sequence(
                            fn (Sequence $sequence) => [
                                'name' => $sequence->index == $idxTest ? $testname : $defaultName,
                            ]
                        ))
                )
            )
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->cashAccountActions->readAny(
            companyId: $company->id,
            useCache: true,
            withTrashed: false,

            search: 'testing',

            paginate: true,
            page: 1,
            perPage: 10,
            limit: null
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 1);
    }

    public function test_cash_account_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $cashAccountCount = 3;

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory())
                ->has(CashAccount::factory()->count($cashAccountCount)->state(function (array $attributes, Company $company) {
                    return [
                        'branch_id' => $company->branches()->inRandomOrder()->first()->id,
                    ];
                }))
            )
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->cashAccountActions->readAny(
            companyId: $company->id,
            useCache: true,
            withTrashed: false,

            search: '',

            paginate: true,
            page: -1,
            perPage: 10,
            limit: null
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == $cashAccountCount);
    }

    public function test_cash_account_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    CashAccount::factory()->state(function (array $attributes, Company $company) {
                        return [
                            'branch_id' => $company->branches()->inRandomOrder()->first()->id,
                        ];
                    })
                )
            )->create();

        $cashAccount = $user->companies()->inRandomOrder()->first()
            ->cashAccounts()->inRandomOrder()->first();

        $result = $this->cashAccountActions->read($cashAccount);

        $this->assertInstanceOf(CashAccount::class, $result);
    }
}
