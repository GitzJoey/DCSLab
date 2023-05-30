<?php

namespace Tests\Unit\Actions\ProductActions;

use App\Actions\Product\ProductActions;
use App\Enums\ProductCategory;
use App\Enums\ProductGroupCategory;
use App\Enums\UnitCategory;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductUnit;
use App\Models\Unit;
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

    public function test_product_actions_call_update_product_and_insert_product_units_expect_db_updated()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()->where('category', '=', ProductGroupCategory::PRODUCTS->value)
            ->inRandomOrder()->first();

        $brand = $company->brands()->inRandomOrder()->first();

        $product = Product::factory()
            ->for($company)
            ->for($productGroup)
            ->for($brand)
            ->setProductTypeAsProduct();

        $units = $company->units()->where('category', '=', UnitCategory::PRODUCTS->value)
            ->inRandomOrder()->get()->shuffle();

        $productUnitCount = random_int(1, $units->count());
        $primaryUnitIdx = random_int(0, $productUnitCount - 1);

        for ($j = 0; $j < $productUnitCount; $j++) {
            $product = $product->has(
                ProductUnit::factory()
                    ->for($company)->for($units[$j])
                    ->setConversionValue($j == 0 ? 1 : random_int(2, 10))
                    ->setIsPrimaryUnit($j == $primaryUnitIdx)
            );
        }

        $product = $product->create();

        $productArr = $product->toArray();

        $productUnitsArr = $product->productUnits->toArray();
        array_push(
            $productUnitsArr,
            ProductUnit::factory()
                ->for($company->units()->where('category', '=', UnitCategory::PRODUCTS->value)->inRandomOrder()->first())
                ->setConversionValue($productUnitsArr[count($productUnitsArr) - 1]['conversion_value'] * 2)
                ->setIsBase(false)
                ->setIsPrimaryUnit(false)
                ->make(['id' => null])->toArray()
        );

        $result = $this->productActions->update(
            $product,
            $productArr,
            $productUnitsArr
        );

        $this->assertInstanceOf(Product::class, $result);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'company_id' => $productArr['company_id'],
            'code' => $productArr['code'],
            'product_group_id' => $productArr['product_group_id'],
            'brand_id' => $productArr['brand_id'],
            'name' => $productArr['name'],
            'taxable_supply' => $productArr['taxable_supply'],
            'standard_rated_supply' => $productArr['standard_rated_supply'],
            'price_include_vat' => $productArr['price_include_vat'],
            'remarks' => $productArr['remarks'],
            'point' => $productArr['point'],
            'use_serial_number' => $productArr['use_serial_number'],
            'has_expiry_date' => $productArr['has_expiry_date'],
            'product_type' => $productArr['product_type'],
            'status' => $productArr['status'],
        ]);

        for ($i = 0; $i < count($productUnitsArr); $i++) {
            $this->assertDatabaseHas('product_units', [
                'company_id' => $company->id,
                'product_id' => $product->id,
                'unit_id' => $productUnitsArr[$i]['unit_id'],
                'code' => $productUnitsArr[$i]['code'],
                'is_base' => $productUnitsArr[$i]['is_base'],
                'conversion_value' => $productUnitsArr[$i]['conversion_value'],
                'is_primary_unit' => $productUnitsArr[$i]['is_primary_unit'],
                'remarks' => $productUnitsArr[$i]['remarks'],
            ]);
        }
    }

    public function test_product_actions_call_update_product_and_edit_product_units_expect_db_updated()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()->where('category', '=', ProductGroupCategory::PRODUCTS->value)
            ->inRandomOrder()->first();

        $brand = $company->brands()->inRandomOrder()->first();

        $product = Product::factory()
            ->for($company)
            ->for($productGroup)
            ->for($brand)
            ->setProductTypeAsProduct();

        $units = $company->units()->where('category', '=', UnitCategory::PRODUCTS->value)
            ->inRandomOrder()->get()->shuffle();

        $productUnitCount = random_int(1, $units->count());
        $primaryUnitIdx = random_int(0, $productUnitCount - 1);

        for ($j = 0; $j < $productUnitCount; $j++) {
            $product = $product->has(
                ProductUnit::factory()
                    ->for($company)->for($units[$j])
                    ->setConversionValue($j == 0 ? 1 : random_int(2, 10))
                    ->setIsPrimaryUnit($j == $primaryUnitIdx)
            );
        }

        $product = $product->create();

        $productArr = $product->toArray();

        $productUnitsArr = $product->productUnits->toArray();
        array_push(
            $productUnitsArr,
            ProductUnit::factory()
                ->for($company->units()->where('category', '=', UnitCategory::PRODUCTS->value)->inRandomOrder()->first())
                ->setConversionValue($productUnitsArr[count($productUnitsArr) - 1]['conversion_value'] * 2)
                ->setIsBase(false)
                ->setIsPrimaryUnit(false)
                ->make(['id' => null])->toArray()
        );

        $lastRow = count($productUnitsArr) - 1;
        $productUnitsArr[$lastRow]['id'] = null;
        $productUnitsArr[$lastRow]['code'] = $this->productActions->generateUniqueCodeForProductUnits();
        $productUnitsArr[$lastRow]['unit_id'] = $company->units()->where('category', '<>', ProductCategory::SERVICES->value)->inRandomOrder()->first()->id;
        $productUnitsArr[$lastRow]['conversion_value'] = $productUnitsArr[$lastRow]['conversion_value'] * 2;
        $productUnitsArr[$lastRow]['is_base'] = false;
        $productUnitsArr[$lastRow]['is_primary_unit'] = false;
        $productUnitsArr[$lastRow]['remarks'] = ProductUnit::factory()->make()->remarks;

        $result = $this->productActions->update(
            $product,
            $productArr,
            $productUnitsArr
        );

        $this->assertInstanceOf(Product::class, $result);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'company_id' => $productArr['company_id'],
            'code' => $productArr['code'],
            'product_group_id' => $productArr['product_group_id'],
            'brand_id' => $productArr['brand_id'],
            'name' => $productArr['name'],
            'taxable_supply' => $productArr['taxable_supply'],
            'standard_rated_supply' => $productArr['standard_rated_supply'],
            'price_include_vat' => $productArr['price_include_vat'],
            'remarks' => $productArr['remarks'],
            'point' => $productArr['point'],
            'use_serial_number' => $productArr['use_serial_number'],
            'has_expiry_date' => $productArr['has_expiry_date'],
            'product_type' => $productArr['product_type'],
            'status' => $productArr['status'],
        ]);

        for ($i = 0; $i < count($productUnitsArr); $i++) {
            $this->assertDatabaseHas('product_units', [
                'company_id' => $company->id,
                'product_id' => $product->id,
                'unit_id' => $productUnitsArr[$i]['unit_id'],
                'code' => $productUnitsArr[$i]['code'],
                'is_base' => $productUnitsArr[$i]['is_base'],
                'conversion_value' => $productUnitsArr[$i]['conversion_value'],
                'is_primary_unit' => $productUnitsArr[$i]['is_primary_unit'],
                'remarks' => $productUnitsArr[$i]['remarks'],
            ]);
        }
    }

    public function test_product_actions_call_update_product_and_delete_product_units_expect_db_updated()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()->where('category', '=', ProductGroupCategory::PRODUCTS->value)
            ->inRandomOrder()->first();

        $brand = $company->brands()->inRandomOrder()->first();

        $product = Product::factory()
            ->for($company)
            ->for($productGroup)
            ->for($brand)
            ->setProductTypeAsProduct();

        $units = $company->units()->where('category', '=', UnitCategory::PRODUCTS->value)
            ->inRandomOrder()->get()->shuffle();

        $productUnitCount = random_int(1, $units->count());
        $primaryUnitIdx = random_int(0, $productUnitCount - 1);

        for ($j = 0; $j < $productUnitCount; $j++) {
            $product = $product->has(
                ProductUnit::factory()
                    ->for($company)->for($units[$j])
                    ->setConversionValue($j == 0 ? 1 : random_int(2, 10))
                    ->setIsPrimaryUnit($j == $primaryUnitIdx)
            );
        }

        $product = $product->create();

        $productArr = $product->toArray();

        $productUnitsArr = $product->productUnits->toArray();

        array_pop($productUnitsArr);

        $result = $this->productActions->update(
            $product,
            $productArr,
            $productUnitsArr
        );

        $this->assertInstanceOf(Product::class, $result);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'company_id' => $productArr['company_id'],
            'code' => $productArr['code'],
            'product_group_id' => $productArr['product_group_id'],
            'brand_id' => $productArr['brand_id'],
            'name' => $productArr['name'],
            'taxable_supply' => $productArr['taxable_supply'],
            'standard_rated_supply' => $productArr['standard_rated_supply'],
            'price_include_vat' => $productArr['price_include_vat'],
            'remarks' => $productArr['remarks'],
            'point' => $productArr['point'],
            'use_serial_number' => $productArr['use_serial_number'],
            'has_expiry_date' => $productArr['has_expiry_date'],
            'product_type' => $productArr['product_type'],
            'status' => $productArr['status'],
        ]);

        for ($i = 0; $i < count($productUnitsArr); $i++) {
            $this->assertDatabaseHas('product_units', [
                'company_id' => $company->id,
                'product_id' => $product->id,
                'unit_id' => $productUnitsArr[$i]['unit_id'],
                'code' => $productUnitsArr[$i]['code'],
                'is_base' => $productUnitsArr[$i]['is_base'],
                'conversion_value' => $productUnitsArr[$i]['conversion_value'],
                'is_primary_unit' => $productUnitsArr[$i]['is_primary_unit'],
                'remarks' => $productUnitsArr[$i]['remarks'],
            ]);
        }
    }

    public function test_product_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()->where('category', '=', ProductGroupCategory::PRODUCTS->value)
            ->inRandomOrder()->first();

        $brand = $company->brands()->inRandomOrder()->first();

        $product = Product::factory()
            ->for($company)
            ->for($productGroup)
            ->for($brand)
            ->setProductTypeAsProduct();

        $units = $company->units()->where('category', '=', UnitCategory::PRODUCTS->value)
            ->inRandomOrder()->get()->shuffle();

        $productUnitCount = random_int(1, $units->count());
        $primaryUnitIdx = random_int(0, $productUnitCount - 1);

        for ($j = 0; $j < $productUnitCount; $j++) {
            $product = $product->has(
                ProductUnit::factory()
                    ->for($company)->for($units[$j])
                    ->setConversionValue($j == 0 ? 1 : random_int(2, 10))
                    ->setIsPrimaryUnit($j == $primaryUnitIdx)
            );
        }

        $product = $product->create();

        $newProductArr = [];
        $newProductUnitsArr = [];

        $this->productActions->update(
            $product,
            $newProductArr,
            $newProductUnitsArr
        );
    }
}
