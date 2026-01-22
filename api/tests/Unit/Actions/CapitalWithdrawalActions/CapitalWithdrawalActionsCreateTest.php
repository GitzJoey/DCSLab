<?php

namespace Tests\Unit\Actions\CapitalWithdrawalActions;

use App\Actions\CapitalWithdrawal\CapitalWithdrawalActions;
use App\Models\Branch;
use App\Models\CapitalWithdrawal;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\Investor;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class CapitalWithdrawalActionsCreateTest extends ActionsTestCase
{
    private CapitalWithdrawalActions $capitalWithdrawalActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->capitalWithdrawalActions = new CapitalWithdrawalActions();
    }

    public function test_capital_withdrawal_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
        $investor = Investor::factory()->for($company)->create();

        $capitalWithdrawalArr = CapitalWithdrawal::factory()->for($company)->make()->toArray();
        $capitalWithdrawalArr['branch_id'] = $branch->id;
        $capitalWithdrawalArr['investor_id'] = $investor->id;
        $capitalWithdrawalArr['cash_account_id'] = $cashAccount->id;

        $result = $this->capitalWithdrawalActions->create($capitalWithdrawalArr);

        $this->assertDatabaseHas('capital_withdrawals', [
            'id' => $result->id,
            'company_id' => $capitalWithdrawalArr['company_id'],
            'branch_id' => $capitalWithdrawalArr['branch_id'],
            'code' => $capitalWithdrawalArr['code'],
            'date' => $capitalWithdrawalArr['date'],
            'investor_id' => $capitalWithdrawalArr['investor_id'],
            'cash_account_id' => $capitalWithdrawalArr['cash_account_id'],
            'amount' => $capitalWithdrawalArr['amount'],
            'remarks' => $capitalWithdrawalArr['remarks'],
        ]);
    }

    public function test_capital_withdrawal_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->capitalWithdrawalActions->create([]);
    }
}
