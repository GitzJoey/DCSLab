<?php

namespace Tests\Unit\Actions\NonCapitalWithdrawalCategoryActions;

use App\Actions\NonCapitalWithdrawalCategory\NonCapitalWithdrawalCategoryActions;
use App\Models\Company;
use App\Models\NonCapitalWithdrawalCategory;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class NonCapitalWithdrawalCategoryActionsCreateTest extends ActionsTestCase
{
    private NonCapitalWithdrawalCategoryActions $nonCapitalWithdrawalCategoryActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nonCapitalWithdrawalCategoryActions = new NonCapitalWithdrawalCategoryActions();
    }

    public function test_non_capital_withdrawal_category_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $nonCapitalWithdrawalCategoryArr = NonCapitalWithdrawalCategory::factory()->for($company)
            ->make()->toArray();

        $result = $this->nonCapitalWithdrawalCategoryActions->create($nonCapitalWithdrawalCategoryArr);

        $this->assertDatabaseHas('non_capital_withdrawal_categories', [
            'id' => $result->id,
            'company_id' => $nonCapitalWithdrawalCategoryArr['company_id'],
            'code' => $nonCapitalWithdrawalCategoryArr['code'],
            'name' => $nonCapitalWithdrawalCategoryArr['name'],
        ]);
    }

    public function test_non_capital_withdrawal_category_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->nonCapitalWithdrawalCategoryActions->create([]);
    }
}
