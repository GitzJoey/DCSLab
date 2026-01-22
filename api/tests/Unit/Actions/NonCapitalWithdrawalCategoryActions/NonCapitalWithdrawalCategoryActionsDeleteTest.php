<?php

namespace Tests\Unit\Actions\NonCapitalWithdrawalCategoryActions;

use App\Actions\NonCapitalWithdrawalCategory\NonCapitalWithdrawalCategoryActions;
use App\Models\Company;
use App\Models\NonCapitalWithdrawalCategory;
use App\Models\User;
use Tests\ActionsTestCase;

class NonCapitalWithdrawalCategoryActionsDeleteTest extends ActionsTestCase
{
    private NonCapitalWithdrawalCategoryActions $nonCapitalWithdrawalCategoryActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nonCapitalWithdrawalCategoryActions = new NonCapitalWithdrawalCategoryActions();
    }

    public function test_non_capital_withdrawal_category_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(NonCapitalWithdrawalCategory::factory())
            )->create();

        $nonCapitalWithdrawalCategory = $user->companies()->inRandomOrder()->first()
            ->nonCapitalWithdrawalCategories()->inRandomOrder()->first();
        $result = $this->nonCapitalWithdrawalCategoryActions->delete($nonCapitalWithdrawalCategory);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('non_capital_withdrawal_categories', [
            'id' => $nonCapitalWithdrawalCategory->id,
        ]);
    }
}
