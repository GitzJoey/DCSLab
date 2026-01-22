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

class CapitalAdditionActionsEditTest extends ActionsTestCase
{
    private CapitalAdditionActions $capitalAdditionActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->capitalAdditionActions = new CapitalAdditionActions();
    }

    public function test_capital_addition_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory())
                ->has(
                    CapitalAddition::factory()->state(function (array $attributes, Company $company) {
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
        $capitalAddition = $company->capitalAdditions()->inRandomOrder()->first();

        $capitalAdditionArr = CapitalAddition::factory()->make()->toArray();
        $capitalAdditionArr['branch_id'] = $capitalAddition->branch_id;
        $capitalAdditionArr['investor_id'] = $capitalAddition->investor_id;
        $capitalAdditionArr['cash_account_id'] = $capitalAddition->cash_account_id;

        $result = $this->capitalAdditionActions->update($capitalAddition, $capitalAdditionArr);

        $this->assertInstanceOf(CapitalAddition::class, $result);
        $this->assertDatabaseHas('capital_additions', [
            'id' => $capitalAddition->id,
            'company_id' => $capitalAddition->company_id,
            'branch_id' => $capitalAdditionArr['branch_id'],
            'code' => $capitalAdditionArr['code'],
            'date' => $capitalAdditionArr['date'],
            'investor_id' => $capitalAdditionArr['investor_id'],
            'cash_account_id' => $capitalAdditionArr['cash_account_id'],
            'amount' => $capitalAdditionArr['amount'],
            'remarks' => $capitalAdditionArr['remarks'],
        ]);
    }

    public function test_capital_addition_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(CapitalAddition::factory())
            )->create();

        $capitalAddition = $user->companies()->inRandomOrder()->first()
            ->capitalAdditions()->inRandomOrder()->first();

        $capitalAdditionArr = [];

        $this->capitalAdditionActions->update($capitalAddition, $capitalAdditionArr);
    }
}
