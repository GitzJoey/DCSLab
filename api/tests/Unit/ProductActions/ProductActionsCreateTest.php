<?php

namespace Tests\Unit\ProductActions;

use App\Actions\Product\ProductActions;
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

class ProductActionsCreateTest extends ActionsTestCase
{
    private ProductActions $productActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productActions = new ProductActions();
    }

    public function test_product_actions_call_create_product_expect_db_has_record()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()
            ->where('category', '=', ProductGroupCategory::PRODUCTS->value)
            ->inRandomOrder()->first();

        $brand = $company->brands()->inRandomOrder()->first();

        $productArr = Product::factory()
            ->for($company)->for($productGroup)->for($brand)
            ->setProductTypeAsProduct()
            ->setStatusActive()
            ->make()->toArray();

        $productUnitArr = [];

        $units = $company->units()
            ->where('category', '=', UnitCategory::PRODUCTS->value)
            ->inRandomOrder()->get()->shuffle();

        $productUnitCount = random_int(1, $units->count());
        $primaryUnitIdx = random_int(0, $productUnitCount - 1);
        for ($j = 0; $j < $productUnitCount; $j++) {
            array_push(
                $productUnitArr,
                ProductUnit::factory()
                    ->for($company)->for($units[$j])
                    ->setConversionValue($j == 0 ? 1 : random_int(2, 10))
                    ->setIsPrimaryUnit($j == $primaryUnitIdx)
                    ->make()->toArray()
            );
        }

        $result = $this->productActions->create(
            $productArr,
            $productUnitArr
        );

        $this->assertDatabaseHas('products', [
            'id' => $result->id,
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

        for ($i = 0; $i < $productUnitCount; $i++) {
            $this->assertDatabaseHas('product_units', [
                'company_id' => $company->id,
                'product_id' => $result->id,
                'unit_id' => $productUnitArr[$i]['unit_id'],
                'code' => $productUnitArr[$i]['code'],
                'is_base' => $productUnitArr[$i]['is_base'],
                'conversion_value' => $productUnitArr[$i]['conversion_value'],
                'is_primary_unit' => $productUnitArr[$i]['is_primary_unit'],
                'remarks' => $productUnitArr[$i]['remarks'],
            ]);
        }
    }

    public function test_product_actions_call_create_service_expect_db_has_record()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToService()->count(5))
                    ->has(Unit::factory()->setCategoryToService()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()
            ->where('category', '=', ProductGroupCategory::SERVICES->value)
            ->inRandomOrder()->first();

        $productArr = Product::factory()
            ->for($company)->for($productGroup)
            ->setProductTypeAsService()
            ->setStatusActive()
            ->make(['brand_id' => null])
            ->toArray();

        $unit = $company->units()
            ->where('category', '=', UnitCategory::SERVICES->value)
            ->inRandomOrder()->first();

        $productUnitArr = [];
        array_push(
            $productUnitArr,
            ProductUnit::factory()
                ->for($unit)
                ->setIsBase(true)
                ->setIsPrimaryUnit(true)
                ->setConversionValue(1)
                ->make()->toArray()
        );

        $result = $this->productActions->create(
            $productArr,
            $productUnitArr
        );

        $this->assertDatabaseHas('products', [
            'id' => $result->id,
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

        $this->assertDatabaseHas('product_units', [
            'company_id' => $company->id,
            'product_id' => $result->id,
            'unit_id' => $productUnitArr[0]['unit_id'],
            'code' => $productUnitArr[0]['code'],
            'is_base' => $productUnitArr[0]['is_base'],
            'conversion_value' => $productUnitArr[0]['conversion_value'],
            'is_primary_unit' => $productUnitArr[0]['is_primary_unit'],
            'remarks' => $productUnitArr[0]['remarks'],
        ]);
    }

    public function test_product_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->productActions->create(
            [],
            []
        );
    }
}
