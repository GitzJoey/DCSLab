<?php

namespace Tests\Unit\Actions\CashAccountActions;

use App\Actions\CashAccount\CashAccountActions;
use App\DTOs\CashAccountUpdateDTO;
use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\User;
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

        $payload = CashAccount::factory()->make()->toArray();
        $payload['company_id'] = $company->id;

        $dto = new CashAccountUpdateDTO(
            code: $payload['code'],
            name: $payload['name'],
            isBank: $payload['is_bank'],
            isActive: $payload['is_active'],
            remarks: $payload['remarks']
        );

        $result = $this->cashAccountActions->update($cashAccount, $dto);
        $this->assertInstanceOf(CashAccount::class, $result);
        $this->assertDatabaseHas('cash_accounts', [
            'id' => $cashAccount->id,
            'company_id' => $cashAccount->company_id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_cash_account_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(\ArgumentCountError::class);
        $dtoClass = \App\DTOs\CashAccountUpdateDTO::class;

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

        $dto = new $dtoClass(...[]);

        $this->cashAccountActions->update($cashAccount, $dto);
    }
}
