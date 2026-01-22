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

class CapitalWithdrawalActionsEditTest extends ActionsTestCase
{
    private CapitalWithdrawalActions $capitalWithdrawalActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->capitalWithdrawalActions = new CapitalWithdrawalActions();
    }

    public function test_capital_withdrawal_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    CapitalWithdrawal::factory()->state(function (array $attributes, Company $company) {
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
        $capitalWithdrawal = $company->capitalWithdrawals()->inRandomOrder()->first();

        $capitalWithdrawalArr = CapitalWithdrawal::factory()->make()->toArray();
        $capitalWithdrawalArr['branch_id'] = $capitalWithdrawal->branch_id;
        $capitalWithdrawalArr['investor_id'] = $capitalWithdrawal->investor_id;
        $capitalWithdrawalArr['cash_account_id'] = $capitalWithdrawal->cash_account_id;

        $result = $this->capitalWithdrawalActions->update($capitalWithdrawal, $capitalWithdrawalArr);

        $this->assertInstanceOf(CapitalWithdrawal::class, $result);
        $this->assertDatabaseHas('capital_withdrawals', [
            'id' => $capitalWithdrawal->id,
            'company_id' => $capitalWithdrawal->company_id,
            'branch_id' => $capitalWithdrawalArr['branch_id'],
            'code' => $capitalWithdrawalArr['code'],
            'date' => $capitalWithdrawalArr['date'],
            'investor_id' => $capitalWithdrawalArr['investor_id'],
            'cash_account_id' => $capitalWithdrawalArr['cash_account_id'],
            'amount' => $capitalWithdrawalArr['amount'],
            'remarks' => $capitalWithdrawalArr['remarks'],
        ]);
    }

    public function test_capital_withdrawal_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(CapitalWithdrawal::factory())
            )->create();

        $capitalWithdrawal = $user->companies()->inRandomOrder()->first()
            ->capitalWithdrawals()->inRandomOrder()->first();

        $capitalWithdrawalArr = [];

        $this->capitalWithdrawalActions->update($capitalWithdrawal, $capitalWithdrawalArr);
    }
}
