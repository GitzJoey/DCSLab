<?php

namespace Tests\Unit\Actions\NonCapitalAdditionCategoryActions;

use App\Actions\NonCapitalAdditionCategory\NonCapitalAdditionCategoryActions;
use App\Models\Company;
use App\Models\NonCapitalAdditionCategory;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class NonCapitalAdditionCategoryActionsEditTest extends ActionsTestCase
{
    private NonCapitalAdditionCategoryActions $nonCapitalAdditionCategoryActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nonCapitalAdditionCategoryActions = new NonCapitalAdditionCategoryActions();
    }

    public function test_non_capital_addition_category_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(NonCapitalAdditionCategory::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $nonCapitalAdditionCategory = $company->nonCapitalAdditionCategories()->inRandomOrder()->first();

        $nonCapitalAdditionCategoryArr = NonCapitalAdditionCategory::factory()->make()->toArray();

        $result = $this->nonCapitalAdditionCategoryActions->update($nonCapitalAdditionCategory, $nonCapitalAdditionCategoryArr);

        $this->assertInstanceOf(NonCapitalAdditionCategory::class, $result);
        $this->assertDatabaseHas('non_capital_addition_categories', [
            'id' => $nonCapitalAdditionCategory->id,
            'company_id' => $nonCapitalAdditionCategory->company_id,
            'code' => $nonCapitalAdditionCategoryArr['code'],
            'name' => $nonCapitalAdditionCategoryArr['name'],
        ]);
    }

    public function test_non_capital_addition_category_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(NonCapitalAdditionCategory::factory())
            )->create();

        $nonCapitalAdditionCategory = $user->companies()->inRandomOrder()->first()
            ->nonCapitalAdditionCategories()->inRandomOrder()->first();

        $nonCapitalAdditionCategoryArr = [];

        $this->nonCapitalAdditionCategoryActions->update($nonCapitalAdditionCategory, $nonCapitalAdditionCategoryArr);
    }
}
