<?php

namespace Tests\Unit\Actions\CashAccountActions;

use App\Actions\CashAccount\CashAccountActions;
use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\User;
use Tests\ActionsTestCase;

class CashAccountActionsCreateTest extends ActionsTestCase
{
    private CashAccountActions $cashAccountActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashAccountActions = new CashAccountActions();
    }

    public function test_cash_account_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        $cashAccountArr = CashAccount::factory()->for($company)
            ->make()->toArray();
        $cashAccountArr['branch_id'] = $branch->id;

        $result = $this->cashAccountActions->create(new \App\DTOs\CashAccountCreateDTO(
            companyId: $cashAccountArr['company_id'],
            branchId: $cashAccountArr['branch_id'],
            code: $cashAccountArr['code'],
            name: $cashAccountArr['name'],
            isBank: $cashAccountArr['is_bank'],
            isActive: $cashAccountArr['is_active'],
            remarks: $cashAccountArr['remarks']
        ));

        $this->assertDatabaseHas('cash_accounts', [
            'id' => $result->id,
            'company_id' => $cashAccountArr['company_id'],
            'code' => $cashAccountArr['code'],
            'name' => $cashAccountArr['name'],
        ]);
    }

    public function test_cash_account_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->cashAccountActions->create(new \App\DTOs\CashAccountCreateDTO());

    }
}
