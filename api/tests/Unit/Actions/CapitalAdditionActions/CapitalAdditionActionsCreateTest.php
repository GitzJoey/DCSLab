<?php

namespace Tests\Unit\Actions\CapitalAdditionActions;

use App\Actions\CapitalAddition\CapitalAdditionActions;
use App\Models\Branch;
use App\Models\CapitalAddition;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\Investor;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class CapitalAdditionActionsCreateTest extends ActionsTestCase
{
    private CapitalAdditionActions $capitalAdditionActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->capitalAdditionActions = new CapitalAdditionActions();
    }

    public function test_capital_addition_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
        $investor = Investor::factory()->for($company)->create();

        $capitalAdditionArr = CapitalAddition::factory()->for($company)->make()->toArray();
        $capitalAdditionArr['branch_id'] = $branch->id;
        $capitalAdditionArr['investor_id'] = $investor->id;
        $capitalAdditionArr['cash_account_id'] = $cashAccount->id;

        $result = $this->capitalAdditionActions->create($capitalAdditionArr);

        $this->assertDatabaseHas('capital_additions', [
            'id' => $result->id,
            'company_id' => $capitalAdditionArr['company_id'],
            'branch_id' => $capitalAdditionArr['branch_id'],
            'code' => $capitalAdditionArr['code'],
            'date' => $capitalAdditionArr['date'],
            'investor_id' => $capitalAdditionArr['investor_id'],
            'cash_account_id' => $capitalAdditionArr['cash_account_id'],
            'amount' => $capitalAdditionArr['amount'],
            'remarks' => $capitalAdditionArr['remarks'],
        ]);
    }

    public function test_capital_addition_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->capitalAdditionActions->create([]);
    }
}
