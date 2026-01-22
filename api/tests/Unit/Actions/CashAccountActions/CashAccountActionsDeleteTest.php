<?php

namespace Tests\Unit\Actions\CashAccountActions;

use App\Actions\CashAccount\CashAccountActions;
use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\User;
use Tests\ActionsTestCase;

class CashAccountActionsDeleteTest extends ActionsTestCase
{
    private CashAccountActions $cashAccountActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashAccountActions = new CashAccountActions();
    }

    public function test_cash_account_actions_call_delete_expect_bool()
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
        $result = $this->cashAccountActions->delete($cashAccount);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('cash_accounts', [
            'id' => $cashAccount->id,
        ]);
    }
}
