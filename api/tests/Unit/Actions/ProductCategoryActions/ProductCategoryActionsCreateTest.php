<?php

namespace Tests\Unit\Actions\ProductCategoryActions;

use App\Actions\ProductCategory\ProductCategoryActions;
use App\Models\Company;
use App\Models\ProductCategory;
use App\Models\User;
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

        $payload = ProductCategory::factory()->for($company)
            ->make()->toArray();

        $dto = new \App\DTOs\ProductCategoryCreateDTO(
            companyId: $payload['company_id'],
            code: $payload['code'],
            name: $payload['name'],
            type: $payload['type']
        );

        $result = $this->productCategoryActions->create($dto);
        $this->assertDatabaseHas('product_categories', [
            'id' => $result->id,
            'company_id' => $payload['company_id'],
            'code' => $payload['code'],
            'name' => $payload['name'],
            'type' => $payload['type'],
        ]);
    }

    public function test_product_category_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $dto = new \App\DTOs\ProductCategoryCreateDTO();

        $this->productCategoryActions->create($dto);
    }
}
