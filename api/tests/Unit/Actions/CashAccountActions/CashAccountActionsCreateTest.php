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

        $payload = CashAccount::factory()->for($company)
            ->make()->toArray();
        $payload['branch_id'] = $branch->id;

        $dto = new \App\DTOs\CashAccountCreateDTO(
            companyId: $payload['company_id'],
            branchId: $payload['branch_id'],
            code: $payload['code'],
            name: $payload['name'],
            isBank: $payload['is_bank'],
            isActive: $payload['is_active'],
            remarks: $payload['remarks']
        );

        $result = $this->cashAccountActions->create($dto);
        $this->assertDatabaseHas('cash_accounts', [
            'id' => $result->id,
            'company_id' => $payload['company_id'],
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_cash_account_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $dto = new \App\DTOs\CashAccountCreateDTO();

        $this->cashAccountActions->create($dto);
    }
}
