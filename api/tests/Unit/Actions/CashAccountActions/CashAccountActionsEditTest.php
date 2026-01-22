<?php

namespace Tests\Unit\Actions\CashAccountActions;

use App\Actions\CashAccount\CashAccountActions;
use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class CashAccountActionsEditTest extends ActionsTestCase
{
    private CashAccountActions $cashAccountActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashAccountActions = new CashAccountActions();
    }

    public function test_cash_account_actions_call_update_expect_db_updated()
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

        $company = $user->companies()->inRandomOrder()->first();
        $cashAccount = $company->cashAccounts()->inRandomOrder()->first();

        $cashAccountArr = CashAccount::factory()->make()->toArray();
        $cashAccountArr['company_id'] = $company->id;

        $result = $this->cashAccountActions->update($cashAccount, $cashAccountArr);

        $this->assertInstanceOf(CashAccount::class, $result);
        $this->assertDatabaseHas('cash_accounts', [
            'id' => $cashAccount->id,
            'company_id' => $cashAccount->company_id,
            'code' => $cashAccountArr['code'],
            'name' => $cashAccountArr['name'],
        ]);
    }

    public function test_cash_account_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

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

        $cashAccountArr = [];

        $this->cashAccountActions->update($cashAccount, $cashAccountArr);
    }
}
