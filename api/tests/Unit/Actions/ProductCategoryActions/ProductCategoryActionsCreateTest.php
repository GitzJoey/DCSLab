<?php

namespace Tests\Unit\Actions\ProductCategoryActions;

use App\Actions\ProductCategory\ProductCategoryActions;
use App\Models\Company;
use App\Models\ProductCategory;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class ProductCategoryActionsCreateTest extends ActionsTestCase
{
    private ProductCategoryActions $productCategoryActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productCategoryActions = new ProductCategoryActions();
    }

    public function test_product_category_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productCategoryArr = ProductCategory::factory()->for($company)
            ->make()->toArray();

        $result = $this->productCategoryActions->create($productCategoryArr);

        $this->assertDatabaseHas('product_categories', [
            'id' => $result->id,
            'company_id' => $productCategoryArr['company_id'],
            'code' => $productCategoryArr['code'],
            'name' => $productCategoryArr['name'],
            'type' => $productCategoryArr['type'],
        ]);
    }

    public function test_product_category_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->productCategoryActions->create([]);
    }
}
