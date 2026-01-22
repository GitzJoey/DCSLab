<?php

namespace Tests\Unit\Actions\NonCapitalAdditionCategoryActions;

use App\Actions\NonCapitalAdditionCategory\NonCapitalAdditionCategoryActions;
use App\Models\Company;
use App\Models\NonCapitalAdditionCategory;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class NonCapitalAdditionCategoryActionsCreateTest extends ActionsTestCase
{
    private NonCapitalAdditionCategoryActions $nonCapitalAdditionCategoryActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nonCapitalAdditionCategoryActions = new NonCapitalAdditionCategoryActions();
    }

    public function test_non_capital_addition_category_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $nonCapitalAdditionCategoryArr = NonCapitalAdditionCategory::factory()->for($company)
            ->make()->toArray();

        $result = $this->nonCapitalAdditionCategoryActions->create($nonCapitalAdditionCategoryArr);

        $this->assertDatabaseHas('non_capital_addition_categories', [
            'id' => $result->id,
            'company_id' => $nonCapitalAdditionCategoryArr['company_id'],
            'code' => $nonCapitalAdditionCategoryArr['code'],
            'name' => $nonCapitalAdditionCategoryArr['name'],
        ]);
    }

    public function test_non_capital_addition_category_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->nonCapitalAdditionCategoryActions->create([]);
    }
}
