<?php

namespace Tests\Unit\Actions\CapitalWithdrawalActions;

use App\Actions\CapitalWithdrawal\CapitalWithdrawalActions;
use App\Models\Branch;
use App\Models\CapitalWithdrawal;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\Investor;
use App\Models\User;
use Tests\ActionsTestCase;

class CapitalWithdrawalActionsDeleteTest extends ActionsTestCase
{
    private CapitalWithdrawalActions $capitalWithdrawalActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->capitalWithdrawalActions = new CapitalWithdrawalActions();
    }

    public function test_capital_withdrawal_actions_call_delete_expect_bool()
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

        $capitalWithdrawal = $user->companies()->inRandomOrder()->first()
            ->capitalWithdrawals()->inRandomOrder()->first();
        $result = $this->capitalWithdrawalActions->delete($capitalWithdrawal);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('capital_withdrawals', [
            'id' => $capitalWithdrawal->id,
        ]);
    }
}
