<?php

namespace Tests\Unit\Actions\ProductCategoryActions;

use App\Actions\ProductCategory\ProductCategoryActions;
use App\Models\Company;
use App\Models\ProductCategory;
use App\Models\User;
use Tests\ActionsTestCase;

class ProductCategoryActionsDeleteTest extends ActionsTestCase
{
    private ProductCategoryActions $productCategoryActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productCategoryActions = new ProductCategoryActions();
    }

    public function test_product_category_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductCategory::factory())
            )->create();

        $productCategory = $user->companies()->inRandomOrder()->first()
            ->productCategories()->inRandomOrder()->first();
        $result = $this->productCategoryActions->delete($productCategory);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('product_categories', [
            'id' => $productCategory->id,
        ]);
    }
}
