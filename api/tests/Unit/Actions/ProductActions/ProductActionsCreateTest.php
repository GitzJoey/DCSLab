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

        $productArr = Product::factory()
            ->for($company)
            ->for($productCategory, 'category')
            ->for($brand)
            ->make()->toArray();

        $productUnit = \App\Models\ProductUnit::factory()->make([
            'company_id' => $company->id,
            'unit_id' => \App\Models\Unit::factory()->create(['company_id' => $company->id])->id,
            'point' => 50,
        ])->toArray();
        $productArr['product_units'] = [$productUnit];

        $result = $this->productActions->create($productArr);

        $this->assertDatabaseHas('products', [
            'id' => $result->id,
            'company_id' => $productArr['company_id'],
            'category_id' => $productArr['category_id'],
            'brand_id' => $productArr['brand_id'],
            'code' => $productArr['code'],
            'name' => $productArr['name'],
            'type' => $productArr['type'],
            'is_taxable' => $productArr['is_taxable'],
            'vat_rate' => $productArr['vat_rate'],
            'is_price_include_vat' => $productArr['is_price_include_vat'],
            'is_use_serial_number' => $productArr['is_use_serial_number'],
            'is_expirable' => $productArr['is_expirable'],
            'status' => $productArr['status'],
            'remarks' => $productArr['remarks'],
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
