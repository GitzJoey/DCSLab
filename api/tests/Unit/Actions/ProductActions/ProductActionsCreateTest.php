<?php

namespace Tests\Unit\Actions\ProductActions;

use App\Actions\Product\ProductActions;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class ProductActionsCreateTest extends ActionsTestCase
{
    private ProductActions $productActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productActions = new ProductActions();
    }

    public function test_product_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductCategory::factory()->count(3))
                ->has(Brand::factory()->count(3)))
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productCategory = $company->productCategories()->inRandomOrder()->first();
        $brand = $company->brands()->inRandomOrder()->first();

        $payload = Product::factory()
            ->for($company)
            ->for($productCategory, 'category')
            ->for($brand)
            ->make()->toArray();

        $productUnit = \App\Models\ProductUnit::factory()->make([
            'company_id' => $company->id,
            'unit_id' => \App\Models\Unit::factory()->create(['company_id' => $company->id])->id,
            'point' => 50,
        ])->toArray();
        $payload['product_units'] = [$productUnit];

        $result = $this->productActions->create($payload);

        $this->assertDatabaseHas('products', [
            'id' => $result->id,
            'company_id' => $payload['company_id'],
            'category_id' => $payload['category_id'],
            'brand_id' => $payload['brand_id'],
            'code' => $payload['code'],
            'name' => $payload['name'],
            'type' => $payload['type'],
            'is_price_include_vat' => $payload['is_price_include_vat'],
            'is_use_serial_number' => $payload['is_use_serial_number'],
            'is_expirable' => $payload['is_expirable'],
            'status' => $payload['status'],
            'remarks' => $payload['remarks'],
        ]);

        $this->assertDatabaseHas('product_units', [
            'product_id' => $result->id,
            'point' => 50,
        ]);
    }

    public function test_product_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->productActions->create([]);
    }
}
