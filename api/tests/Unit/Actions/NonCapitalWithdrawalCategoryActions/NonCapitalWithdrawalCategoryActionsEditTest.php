<?php

namespace Tests\Unit\Actions\NonCapitalWithdrawalCategoryActions;

use App\Actions\NonCapitalWithdrawalCategory\NonCapitalWithdrawalCategoryActions;
use App\Models\Company;
use App\Models\NonCapitalWithdrawalCategory;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class NonCapitalWithdrawalCategoryActionsEditTest extends ActionsTestCase
{
    private NonCapitalWithdrawalCategoryActions $nonCapitalWithdrawalCategoryActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nonCapitalWithdrawalCategoryActions = new NonCapitalWithdrawalCategoryActions();
    }

    public function test_non_capital_withdrawal_category_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(NonCapitalWithdrawalCategory::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $nonCapitalWithdrawalCategory = $company->nonCapitalWithdrawalCategories()->inRandomOrder()->first();

        $nonCapitalWithdrawalCategoryArr = NonCapitalWithdrawalCategory::factory()->make()->toArray();

        $result = $this->nonCapitalWithdrawalCategoryActions->update($nonCapitalWithdrawalCategory, $nonCapitalWithdrawalCategoryArr);

        $this->assertInstanceOf(NonCapitalWithdrawalCategory::class, $result);
        $this->assertDatabaseHas('non_capital_withdrawal_categories', [
            'id' => $nonCapitalWithdrawalCategory->id,
            'company_id' => $nonCapitalWithdrawalCategory->company_id,
            'code' => $nonCapitalWithdrawalCategoryArr['code'],
            'name' => $nonCapitalWithdrawalCategoryArr['name'],
        ]);
    }

    public function test_non_capital_withdrawal_category_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(NonCapitalWithdrawalCategory::factory())
            )->create();

        $nonCapitalWithdrawalCategory = $user->companies()->inRandomOrder()->first()
            ->nonCapitalWithdrawalCategories()->inRandomOrder()->first();

        $nonCapitalWithdrawalCategoryArr = [];

        $this->nonCapitalWithdrawalCategoryActions->update($nonCapitalWithdrawalCategory, $nonCapitalWithdrawalCategoryArr);
    }
}
