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

class ProductActionsEditTest extends ActionsTestCase
{
    private ProductActions $productActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productActions = new ProductActions();
    }

    public function test_product_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductCategory::factory()->count(3))
                ->has(Brand::factory()->count(3)))
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productCategory = $company->productCategories()->inRandomOrder()->first();

        $brand = $company->brands()->inRandomOrder()->first();

        $product = Product::factory()
            ->for($company)
            ->for($productCategory, 'category')
            ->for($brand);

        $product = $product->create();

        $productUnit = \App\Models\ProductUnit::factory()->for($product)->create(['point' => 10]);

        $productArr = $product->toArray();
        $productArr['product_units'] = [
            [
                'id' => $productUnit->id,
                'code' => $productUnit->code,
                'unit_id' => $productUnit->unit_id,
                'is_manufacturer_sku' => $productUnit->is_manufacturer_sku,
                'is_base' => $productUnit->is_base,
                'conversion_value' => $productUnit->conversion_value,
                'is_primary_unit' => $productUnit->is_primary_unit,
                'point' => 100,
                'remarks' => $productUnit->remarks,
            ],
        ];

        $result = $this->productActions->update($product, $productArr);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'category_id' => $productArr['category_id'],
            'brand_id' => $productArr['brand_id'],
            'company_id' => $product->company_id,
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
            'id' => $productUnit->id,
            'point' => 100,
        ]);
    }

    public function test_product_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Product::factory())
            )->create();

        $product = $user->companies()->inRandomOrder()->first()
            ->products()->inRandomOrder()->first();

        $productArr = [];

        $this->productActions->update($product, $productArr);
    }
}
