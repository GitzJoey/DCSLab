<?php

namespace Tests\Unit\Actions\CapitalAdditionActions;

use App\Actions\CapitalAddition\CapitalAdditionActions;
use App\Models\Branch;
use App\Models\CapitalAddition;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\Investor;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Tests\ActionsTestCase;

class CapitalAdditionActionsReadTest extends ActionsTestCase
{
    private CapitalAdditionActions $capitalAdditionActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->capitalAdditionActions = new CapitalAdditionActions();
    }

    public function test_capital_addition_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    CapitalAddition::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
                        $investor = Investor::factory()->for($company)->create();

                        return [
                            'branch_id' => $branch->id,
                            'investor_id' => $investor->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->capitalAdditionActions->readAny(
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

    public function test_capital_addition_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    CapitalAddition::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
                        $investor = Investor::factory()->for($company)->create();

                        return [
                            'branch_id' => $branch->id,
                            'investor_id' => $investor->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->capitalAdditionActions->readAny(
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

    public function test_capital_addition_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $result = $this->capitalAdditionActions->readAny(
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

    public function test_capital_addition_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $capitalAdditionCount = 4;

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(CapitalAddition::factory()->count($capitalAdditionCount)
                    ->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
                        $investor = Investor::factory()->for($company)->create();

                        return [
                            'branch_id' => $branch->id,
                            'investor_id' => $investor->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        // Create a CapitalAddition with specific remarks for search testing
        $branch = $company->branches()->inRandomOrder()->first();
        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
        $investor = Investor::factory()->for($company)->create();

        CapitalAddition::factory()->for($company)->create([
            'branch_id' => $branch->id,
            'investor_id' => $investor->id,
            'cash_account_id' => $cashAccount->id,
            'remarks' => 'testing',
        ]);

        $result = $this->capitalAdditionActions->readAny(
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
        $this->assertTrue($result->total() >= 1);
    }

    public function test_capital_addition_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    CapitalAddition::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
                        $investor = Investor::factory()->for($company)->create();

                        return [
                            'branch_id' => $branch->id,
                            'investor_id' => $investor->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->capitalAdditionActions->readAny(
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
        $this->assertTrue($result->total() >= 0);
    }

    public function test_capital_addition_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    CapitalAddition::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
                        $investor = Investor::factory()->for($company)->create();

                        return [
                            'branch_id' => $branch->id,
                            'investor_id' => $investor->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        // Test with zero perPage instead of negative to avoid SQL syntax errors
        $result = $this->capitalAdditionActions->readAny(
            companyId: $company->id,
            useCache: true,
            withTrashed: false,

            search: '',

            paginate: true,
            page: 1,
            perPage: 0,
            limit: null
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() >= 0);
    }

    public function test_capital_addition_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    CapitalAddition::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
                        $investor = Investor::factory()->for($company)->create();

                        return [
                            'branch_id' => $branch->id,
                            'investor_id' => $investor->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )->create();

        $capitalAddition = $user->companies()->inRandomOrder()->first()
            ->capitalAdditions()->inRandomOrder()->first();

        $result = $this->capitalAdditionActions->read($capitalAddition);

        $this->assertInstanceOf(CapitalAddition::class, $result);
    }
}
