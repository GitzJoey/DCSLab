<?php

namespace Tests\Unit\Actions\NonCapitalAdditionCategoryActions;

use App\Actions\NonCapitalAdditionCategory\NonCapitalAdditionCategoryActions;
use App\Models\Company;
use App\Models\NonCapitalAdditionCategory;
use App\Models\User;
use Tests\ActionsTestCase;

class NonCapitalAdditionCategoryActionsDeleteTest extends ActionsTestCase
{
    private NonCapitalAdditionCategoryActions $nonCapitalAdditionCategoryActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nonCapitalAdditionCategoryActions = new NonCapitalAdditionCategoryActions();
    }

    public function test_non_capital_addition_category_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(NonCapitalAdditionCategory::factory())
            )->create();

        $nonCapitalAdditionCategory = $user->companies()->inRandomOrder()->first()
            ->nonCapitalAdditionCategories()->inRandomOrder()->first();
        $result = $this->nonCapitalAdditionCategoryActions->delete($nonCapitalAdditionCategory);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('non_capital_addition_categories', [
            'id' => $nonCapitalAdditionCategory->id,
        ]);
    }
}
