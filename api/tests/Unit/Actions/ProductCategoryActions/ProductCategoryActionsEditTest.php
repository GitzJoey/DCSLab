<?php

namespace Tests\Unit\Actions\ProductCategoryActions;

use App\Actions\ProductCategory\ProductCategoryActions;
use App\Models\Company;
use App\Models\ProductCategory;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class ProductCategoryActionsEditTest extends ActionsTestCase
{
    private ProductCategoryActions $productCategoryActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productCategoryActions = new ProductCategoryActions();
    }

    public function test_product_category_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductCategory::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $productCategory = $company->productCategories()->inRandomOrder()->first();

        $payload = ProductCategory::factory()->make()->toArray();

        $dto = new \App\DTOs\ProductCategoryUpdateDTO(
            code: $payload['code'],
            name: $payload['name'],
            type: $payload['type']
        );

        $result = $this->productCategoryActions->update($productCategory, $dto);
        $this->assertInstanceOf(ProductCategory::class, $result);
        $this->assertDatabaseHas('product_categories', [
            'id' => $productCategory->id,
            'company_id' => $productCategory->company_id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_product_category_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductCategory::factory())
            )->create();

        $productCategory = $user->companies()->inRandomOrder()->first()
            ->productCategories()->inRandomOrder()->first();

        $payload = [];

        $dto = new \App\DTOs\ProductCategoryUpdateDTO();

        $this->productCategoryActions->update($productCategory, $dto);
    }
}
