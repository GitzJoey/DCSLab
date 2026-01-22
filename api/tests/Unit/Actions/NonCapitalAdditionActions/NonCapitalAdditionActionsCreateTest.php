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

class NonCapitalAdditionActionsCreateTest extends ActionsTestCase
{
    private NonCapitalAdditionActions $nonCapitalAdditionActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nonCapitalAdditionActions = new NonCapitalAdditionActions();
    }

    public function test_non_capital_addition_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()->setStatusActive()->setIsMainBranch()))
            ->create();

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        $category = NonCapitalAdditionCategory::factory()->for($company)->create();
        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);

        $nonCapitalAdditionArr = NonCapitalAddition::factory()->for($company)->make()->toArray();
        $nonCapitalAdditionArr['branch_id'] = $branch->id;
        $nonCapitalAdditionArr['category_id'] = $category->id;
        $nonCapitalAdditionArr['cash_account_id'] = $cashAccount->id;

        $result = $this->nonCapitalAdditionActions->create($nonCapitalAdditionArr);

        $this->assertDatabaseHas('non_capital_additions', [
            'id' => $result->id,
            'company_id' => $nonCapitalAdditionArr['company_id'],
            'branch_id' => $nonCapitalAdditionArr['branch_id'],
            'code' => $nonCapitalAdditionArr['code'],
            'date' => $nonCapitalAdditionArr['date'],
            'category_id' => $nonCapitalAdditionArr['category_id'],
            'cash_account_id' => $nonCapitalAdditionArr['cash_account_id'],
            'amount' => $nonCapitalAdditionArr['amount'],
            'remarks' => $nonCapitalAdditionArr['remarks'],
        ]);
    }

    public function test_non_capital_addition_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->nonCapitalAdditionActions->create([]);
    }
}
