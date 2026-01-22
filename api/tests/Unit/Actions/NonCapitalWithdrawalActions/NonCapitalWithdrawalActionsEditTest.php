<?php

namespace Tests\Unit\Actions\NonCapitalWithdrawalActions;

use App\Actions\NonCapitalWithdrawal\NonCapitalWithdrawalActions;
use App\Models\Company;
use App\Models\NonCapitalWithdrawal;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class NonCapitalWithdrawalActionsEditTest extends ActionsTestCase
{
    private NonCapitalWithdrawalActions $nonCapitalWithdrawalActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nonCapitalWithdrawalActions = new NonCapitalWithdrawalActions();
    }

    public function test_non_capital_withdrawal_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(NonCapitalWithdrawal::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $nonCapitalWithdrawal = $company->nonCapitalWithdrawals()->inRandomOrder()->first();

        $nonCapitalWithdrawalArr = NonCapitalWithdrawal::factory()->make()->toArray();

        $result = $this->nonCapitalWithdrawalActions->update($nonCapitalWithdrawal, $nonCapitalWithdrawalArr);

        $this->assertInstanceOf(NonCapitalWithdrawal::class, $result);
        $this->assertDatabaseHas('non_capital_withdrawals', [
            'id' => $nonCapitalWithdrawal->id,
            'company_id' => $nonCapitalWithdrawal->company_id,
            'code' => $nonCapitalWithdrawalArr['code'],
            'name' => $nonCapitalWithdrawalArr['name'],
        ]);
    }

    public function test_non_capital_withdrawal_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(NonCapitalWithdrawal::factory())
            )->create();

        $nonCapitalWithdrawal = $user->companies()->inRandomOrder()->first()
            ->nonCapitalWithdrawals()->inRandomOrder()->first();

        $nonCapitalWithdrawalArr = [];

        $this->nonCapitalWithdrawalActions->update($nonCapitalWithdrawal, $nonCapitalWithdrawalArr);
    }
}
