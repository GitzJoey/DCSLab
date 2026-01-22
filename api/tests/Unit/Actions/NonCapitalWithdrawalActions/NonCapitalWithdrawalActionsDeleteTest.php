<?php

namespace Tests\Unit\Actions\NonCapitalWithdrawalActions;

use App\Actions\NonCapitalWithdrawal\NonCapitalWithdrawalActions;
use App\Models\Company;
use App\Models\NonCapitalWithdrawal;
use App\Models\User;
use Tests\ActionsTestCase;

class NonCapitalWithdrawalActionsDeleteTest extends ActionsTestCase
{
    private NonCapitalWithdrawalActions $nonCapitalWithdrawalActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nonCapitalWithdrawalActions = new NonCapitalWithdrawalActions();
    }

    public function test_non_capital_withdrawal_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(NonCapitalWithdrawal::factory())
            )->create();

        $nonCapitalWithdrawal = $user->companies()->inRandomOrder()->first()
            ->nonCapitalWithdrawals()->inRandomOrder()->first();
        $result = $this->nonCapitalWithdrawalActions->delete($nonCapitalWithdrawal);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('non_capital_withdrawals', [
            'id' => $nonCapitalWithdrawal->id,
        ]);
    }
}
