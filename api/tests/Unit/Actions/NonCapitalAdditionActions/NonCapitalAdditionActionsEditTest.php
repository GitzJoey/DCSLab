<?php

namespace Tests\Unit\Actions\NonCapitalAdditionActions;

use App\Actions\NonCapitalAddition\NonCapitalAdditionActions;
use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\NonCapitalAddition;
use App\Models\NonCapitalAdditionCategory;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class NonCapitalAdditionActionsEditTest extends ActionsTestCase
{
    private NonCapitalAdditionActions $nonCapitalAdditionActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nonCapitalAdditionActions = new NonCapitalAdditionActions();
    }

    public function test_non_capital_addition_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    NonCapitalAddition::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $category = NonCapitalAdditionCategory::factory()->for($company)->create();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);

                        return [
                            'branch_id' => $branch->id,
                            'category_id' => $category->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $nonCapitalAddition = $company->nonCapitalAdditions()->inRandomOrder()->first();

        $nonCapitalAdditionArr = NonCapitalAddition::factory()->make()->toArray();
        $nonCapitalAdditionArr['branch_id'] = $nonCapitalAddition->branch_id;
        $nonCapitalAdditionArr['category_id'] = $nonCapitalAddition->category_id;
        $nonCapitalAdditionArr['cash_account_id'] = $nonCapitalAddition->cash_account_id;

        $result = $this->nonCapitalAdditionActions->update($nonCapitalAddition, $nonCapitalAdditionArr);

        $this->assertInstanceOf(NonCapitalAddition::class, $result);
        $this->assertDatabaseHas('non_capital_additions', [
            'id' => $nonCapitalAddition->id,
            'company_id' => $nonCapitalAddition->company_id,
            'code' => $nonCapitalAdditionArr['code'],
            'date' => $nonCapitalAdditionArr['date'],
            'category_id' => $nonCapitalAdditionArr['category_id'],
            'cash_account_id' => $nonCapitalAdditionArr['cash_account_id'],
            'amount' => $nonCapitalAdditionArr['amount'],
            'remarks' => $nonCapitalAdditionArr['remarks'],
        ]);
    }

    public function test_non_capital_addition_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    NonCapitalAddition::factory()->state(function (array $attributes, Company $company) {
                        $branch = $company->branches()->inRandomOrder()->first();
                        $category = NonCapitalAdditionCategory::factory()->for($company)->create();
                        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);

                        return [
                            'branch_id' => $branch->id,
                            'category_id' => $category->id,
                            'cash_account_id' => $cashAccount->id,
                        ];
                    })
                )
            )->create();

        $nonCapitalAddition = $user->companies()->inRandomOrder()->first()
            ->nonCapitalAdditions()->inRandomOrder()->first();

        $nonCapitalAdditionArr = [];

        $this->nonCapitalAdditionActions->update($nonCapitalAddition, $nonCapitalAdditionArr);
    }
}
