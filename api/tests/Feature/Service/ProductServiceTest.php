<?php

namespace Tests\Feature\Service;

use App\Enums\ProductCategory;
use Exception;
use App\Models\Unit;
use App\Models\User;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use Tests\ServiceTestCase;
use App\Enums\UnitCategory;
use App\Models\ProductUnit;
use App\Models\ProductGroup;
use App\Services\ProductService;
use App\Enums\ProductGroupCategory;
use App\Enums\ProductType;
use Database\Seeders\UnitTableSeeder;
use Database\Seeders\BrandTableSeeder;
use Database\Seeders\ProductTableSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\ProductGroupTableSeeder;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Factories\Sequence;

class ProductServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productService = app(ProductService::class);
        $this->productGroupSeeder = new ProductGroupTableSeeder();
        $this->brandSeeder = new BrandTableSeeder();
        $this->unitSeeder = new UnitTableSeeder();
        $this->productSeeder = new ProductTableSeeder();
    }

    /* #region create */
    public function test_product_service_call_create_product_expect_db_has_record()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);

        $productGroupId = ProductGroup::where('company_id', '=', $companyId)
                            ->where('category', '<>', ProductGroupCategory::SERVICES->value)
                            ->inRandomOrder()->first()->id;

        $brandId = Brand::where('company_id', '=', $companyId)->inRandomOrder()->first()->id;

        $productArr = Product::factory()->make([
            'company_id' => $companyId,
            'product_group_id' => $productGroupId,
            'brand_id' => $brandId,
            'product_type' => $this->faker->randomElement([1, 2, 3]),
        ])->toArray();

        $productUnitsArr = [];
        $unitCount = $this->faker->numberBetween(1, 5);
        $primaryUnitIdx = $this->faker->numberBetween(0, $unitCount - 1);
        $maxConverionValue = 1;
        for ($i = 0; $i < $unitCount ; $i++) {
            $unitId = Unit::where('company_id', '=', $companyId)
                        ->where('category', '<>', ProductGroupCategory::SERVICES->value)
                        ->inRandomOrder()->first()->id;

            $conversionValue = $i == 0 ? 1 : $this->faker->numberBetween($maxConverionValue + 1, $maxConverionValue + $this->faker->numberBetween(1, 10));
            $isBase = $i == 0 ? true : false;
            $isPrimaryUnit = $i == $primaryUnitIdx ? true : false;

            $productUnitArr = ProductUnit::factory()->make([
                'unit_id' => $unitId,
                'conversion_value' => $conversionValue,
                'is_base' => $isBase,
                'is_primary_unit' => $isPrimaryUnit
            ])->toArray();

            array_push($productUnitsArr, $productUnitArr);

            $maxConverionValue = $productUnitArr['conversion_value'];
        }

        $result = $this->productService->create(
            $productArr,
            $productUnitsArr
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

        for ($i = 0; $i < $unitCount ; $i++) {
            $this->assertDatabaseHas('product_units', [
                'company_id' => $companyId,
                'product_id' => $result->id,
                'unit_id' => $productUnitsArr[$i]['unit_id'],
                'code' => $productUnitsArr[$i]['code'],
                'is_base' => $productUnitsArr[$i]['is_base'],
                'conversion_value' => $productUnitsArr[$i]['conversion_value'],
                'is_primary_unit' => $productUnitsArr[$i]['is_primary_unit'],
                'remarks' => $productUnitsArr[$i]['remarks'],
            ]);
        }
    }

    public function test_product_service_call_create_service_expect_db_has_record()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::SERVICES->value]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::SERVICES->value]);

        $productGroupId = ProductGroup::where('company_id', '=', $companyId)
                            ->where('category', '<>', ProductGroupCategory::PRODUCTS->value)
                            ->inRandomOrder()->first()->id;        

        $productArr = Product::factory()->make([
            'company_id' => $companyId,
            'product_group_id' => $productGroupId,
            'brand_id' => null,
            'product_type' => ProductType::SERVICE->value,
        ])->toArray();

        $unitId = Unit::where('company_id', '=', $companyId)
                    ->where('category', '<>', ProductGroupCategory::PRODUCTS->value)
                    ->inRandomOrder()->first()->id;
        
        $productUnitsArr = [];
        $productUnitArr = ProductUnit::factory()->make([
            'unit_id' => $unitId,
            'conversion_value' => 1,
            'is_base' => true,
            'is_primary_unit' => true
        ])->toArray();
        array_push($productUnitsArr, $productUnitArr);

        $result = $this->productService->create(
            $productArr,
            $productUnitsArr
        );

        $this->assertDatabaseHas('products', [
            'id' => $result->id,
            'company_id' => $productArr['company_id'],
            'code' => $productArr['code'],
            'product_group_id' => $productArr['product_group_id'],
            'brand_id' => null,
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
            'company_id' => $companyId,
            'product_id' => $result->id,
            'unit_id' => $productUnitsArr[0]['unit_id'],
            'code' => $productUnitsArr[0]['code'],
            'is_base' => $productUnitsArr[0]['is_base'],
            'conversion_value' => $productUnitsArr[0]['conversion_value'],
            'is_primary_unit' => $productUnitsArr[0]['is_primary_unit'],
            'remarks' => $productUnitsArr[0]['remarks'],
        ]);
    }

    public function test_product_service_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $result = $this->productService->create(
            [],
            []
        );
    }
    /* #endregion */

    /* #region list */
    public function test_product_service_call_list_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::SERVICES->value]);
        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS_AND_SERVICES->value]);
        
        $this->brandSeeder->callWith(BrandTableSeeder::class, [5, $companyId]);
        
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::SERVICES->value]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS_AND_SERVICES->value]);

        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::SERVICES->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS_AND_SERVICES->value]);

        $result = $this->productService->list(
            companyId: $companyId,
            productCategory: ProductCategory::PRODUCTS->value,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);

        $result = $this->productService->list(
            companyId: $companyId,
            productCategory: ProductCategory::SERVICES->value,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);

        $result = $this->productService->list(
            companyId: $companyId,
            productCategory: ProductCategory::PRODUCTS_AND_SERVICES->value,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_product_service_call_list_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::SERVICES->value]);
        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS_AND_SERVICES->value]);
        
        $this->brandSeeder->callWith(BrandTableSeeder::class, [5, $companyId]);
        
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::SERVICES->value]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS_AND_SERVICES->value]);

        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::SERVICES->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS_AND_SERVICES->value]);

        $result = $this->productService->list(
            companyId: $companyId,
            productCategory: ProductCategory::PRODUCTS->value,
            search: '',
            paginate: false,
        );

        $this->assertInstanceOf(Collection::class, $result);

        $result = $this->productService->list(
            companyId: $companyId,
            productCategory: ProductCategory::SERVICES->value,
            search: '',
            paginate: false,
        );

        $this->assertInstanceOf(Collection::class, $result);

        $result = $this->productService->list(
            companyId: $companyId,
            productCategory: ProductCategory::PRODUCTS_AND_SERVICES->value,
            search: '',
            paginate: false,
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_product_service_call_list_with_nonexistance_company_id_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $result = $this->productService->list(
            companyId: $maxId,
            productCategory: ProductCategory::PRODUCTS->value,
            search: '',
            paginate: false,
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);

        $result = $this->productService->list(
            companyId: $maxId,
            productCategory: ProductCategory::SERVICES->value,
            search: '',
            paginate: false,
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);

        $result = $this->productService->list(
            companyId: $maxId,
            productCategory: ProductCategory::PRODUCTS_AND_SERVICES->value,
            search: '',
            paginate: false,
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_product_service_call_list_with_search_parameter_expect_filtered_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        
        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [5, $companyId, ProductGroupCategory::PRODUCTS_AND_SERVICES->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [5, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [5, $companyId, UnitCategory::PRODUCTS_AND_SERVICES->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::PRODUCTS_AND_SERVICES->value]);

        $productCount = 5;
        $products = $company->products()->take($productCount)->get();
        
        for ($i = 0; $i < $productCount; $i++) {      
            $product = $products[$i];
            $product->name = $product->name . ' testing';
            $product->save();
        }

        $result = $this->productService->list(
            companyId: $companyId,
            productCategory: ProductCategory::PRODUCTS_AND_SERVICES->value,
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == $productCount);
    }

    public function test_product_service_call_list_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS_AND_SERVICES->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS_AND_SERVICES->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [5, $companyId, ProductCategory::PRODUCTS_AND_SERVICES->value]);
        
        $result = $this->productService->list(
            companyId: $companyId, 
            productCategory: ProductCategory::PRODUCTS_AND_SERVICES->value,
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 10);
    }

    public function test_product_service_call_list_products_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS_AND_SERVICES->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS_AND_SERVICES->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [5, $companyId, ProductCategory::PRODUCTS_AND_SERVICES->value]);
        
        $result = $this->productService->list(
            companyId: $companyId, 
            productCategory: ProductCategory::PRODUCTS_AND_SERVICES->value,
            search: '',
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 10);
    }
    /* #endregion */

    /* #region read */
    public function test_product_service_call_read_expect_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        
        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS_AND_SERVICES->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS_AND_SERVICES->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS_AND_SERVICES->value]);

        $product = $company->products()->inRandomOrder()->first();

        $result = $this->productService->read($product);

        $this->assertInstanceOf(Product::class, $result);
    }
    /* #endregion */

    /* #region update */
    public function test_product_service_call_update_product_and_insert_product_units_expect_db_updated()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [5, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [5, $companyId, UnitCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS->value]);

        $product = $company->products()->where('product_type', '<>', ProductType::SERVICE->value)->inRandomOrder()->first();

        $productArr = Product::factory()->setStatusActive()->make([
            'company_id' => $companyId,
            'product_group_id' => ProductGroup::where('company_id', '=', $companyId)->where('category', '<>', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first()->id,
            'brand_id' => Brand::where('company_id', '=', $companyId)->inRandomOrder()->first()->id,
            'product_type' => $this->faker->numberBetween(1, 3)
        ])->toArray();

        $productUnitsArr = $product->productUnits->toArray();
        $newProductUnit = [
            'id' => null,
            'code' => $this->productService->generateUniqueCodeForProductUnits(),
            'unit_id' => $company->units()->where('category', '<>', ProductCategory::SERVICES->value)->inRandomOrder()->first()->id,
            'conversion_value' => $productUnitsArr[count($productUnitsArr) - 1]['conversion_value'] * 2,
            'is_base' => false,
            'is_primary_unit' => false,
            'remarks' => $this->faker->sentence(),
        ];

        array_push($productUnitsArr, $newProductUnit);

        $result = $this->productService->update(
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
        
        for ($i = 0; $i < count($productUnitsArr) ; $i++) {
            $this->assertDatabaseHas('product_units', [
                'company_id' => $companyId,
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

    public function test_product_service_call_update_product_and_edit_product_units_expect_db_updated()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [5, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [5, $companyId, UnitCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS->value]);

        $product = $company->products()->where('product_type', '<>', ProductType::SERVICE->value)->inRandomOrder()->first();

        $productArr = Product::factory()->setStatusActive()->make([
            'company_id' => $companyId,
            'product_group_id' => ProductGroup::where('company_id', '=', $companyId)->where('category', '<>', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first()->id,
            'brand_id' => Brand::where('company_id', '=', $companyId)->inRandomOrder()->first()->id,
            'product_type' => $this->faker->numberBetween(1, 3)
        ])->toArray();
        

        $productUnitsArr = $product->productUnits->toArray();

        $lastRow = count($productUnitsArr) - 1;
        $productUnitsArr[$lastRow]['id'] = null;
        $productUnitsArr[$lastRow]['code'] = $this->productService->generateUniqueCodeForProductUnits();
        $productUnitsArr[$lastRow]['unit_id'] = $company->units()->where('category', '<>', ProductCategory::SERVICES->value)->inRandomOrder()->first()->id;
        $productUnitsArr[$lastRow]['conversion_value'] = $productUnitsArr[$lastRow]['conversion_value'] * 2;
        $productUnitsArr[$lastRow]['is_base'] = false;
        $productUnitsArr[$lastRow]['is_primary_unit'] = false;
        $productUnitsArr[$lastRow]['remarks'] = $this->faker->sentence();

        $result = $this->productService->update(
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
        
        for ($i = 0; $i < count($productUnitsArr) ; $i++) {
            $this->assertDatabaseHas('product_units', [
                'company_id' => $companyId,
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

    public function test_product_service_call_update_product_and_delete_product_units_expect_db_updated()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [5, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [5, $companyId, UnitCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS->value]);

        do {
            $product = $company->products()->where('product_type', '<>', ProductType::SERVICE->value)->inRandomOrder()->first();
        } while ($product->ProductUnits()->count() == 1);

        $productArr = Product::factory()->setStatusActive()->make([
            'company_id' => $companyId,
            'product_group_id' => ProductGroup::where('company_id', '=', $companyId)->where('category', '<>', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first()->id,
            'brand_id' => Brand::where('company_id', '=', $companyId)->inRandomOrder()->first()->id,
            'product_type' => $this->faker->numberBetween(1, 3)
        ])->toArray();

        $productUnitsArr = $product->productUnits->toArray();

        array_pop($productUnitsArr);

        $result = $this->productService->update(
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
        
        for ($i = 0; $i < count($productUnitsArr) ; $i++) {
            $this->assertDatabaseHas('product_units', [
                'company_id' => $companyId,
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

    public function test_product_service_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        
        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [5, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [5, $companyId, UnitCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS->value]);

        $product = $company->products()->inRandomOrder()->first();
        
        $newProductArr = [];
        $newProductUnitsArr = [];

        $this->productService->update(
            $product,
            $newProductArr,
            $newProductUnitsArr
        );
    }
    /* #endregion */

    /* #region delete */
    public function test_product_service_call_delete_expect_bool()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;
        
        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [5, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [5, $companyId, UnitCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS->value]);

        $product = $company->products()->inRandomOrder()->first();

        $result = $this->productService->delete($product);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
        
    }
    /* #endregion */

    /* #region others */
    public function test_product_service_call_function_generate_unique_code_for_product_expect_unique_code_returned()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $code = $this->productService->generateUniqueCodeForProduct();

        $this->assertIsString($code);
        
        $resultCount = $user->companies()->first()->products()->where('code', '=', $code)->count();
        $this->assertTrue($resultCount == 0);
    }

    public function test_product_service_call_function_generate_unique_code_for_product_unit_expect_unique_code_returned()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);

        $productSeeder = new ProductTableSeeder();
        $productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS->value]);

        $code = $this->productService->generateUniqueCodeForProductUnits();

        $this->assertIsString($code);
        
        $resultCount = ProductUnit::where([
            ['company_id', '=', $companyId],
            ['code', '=', $code]
        ])->count();
        $this->assertTrue($resultCount == 0);
    }

    public function test_product_service_call_function_is_unique_code_for_product_expect_can_detect_unique_code()
    {
        $productGroupSeeder = new ProductGroupTableSeeder();
        $brandSeeder = new BrandTableSeeder();
        $unitSeeder = new UnitTableSeeder();
        $productSeeder = new ProductTableSeeder();

        $user = User::factory()
                    ->has(Company::factory()->count(2), 'companies')
                    ->create();

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;

        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId_1, ProductGroupCategory::PRODUCTS->value]);
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId_1]);            
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId_1, UnitCategory::PRODUCTS->value]);
        $productSeeder->callWith(ProductTableSeeder::class, [1, $companyId_1, ProductCategory::PRODUCTS->value]);

        $product_company_1 = $company_1->products()->first();
        $product_company_1->code = 'test1';
        $product_company_1->save();

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;

        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId_2, ProductGroupCategory::PRODUCTS->value]);
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId_2]);            
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId_2, UnitCategory::PRODUCTS->value]);
        $productSeeder->callWith(ProductTableSeeder::class, [1, $companyId_2, ProductCategory::PRODUCTS->value]);

        $product_company_2 = $company_2->products()->first();
        $product_company_2->code = 'test2';
        $product_company_2->save();

        $this->assertFalse($this->productService->isUniqueCodeForProduct('test1', $companyId_1));
        $this->assertTrue($this->productService->isUniqueCodeForProduct('test2', $companyId_1));
        $this->assertTrue($this->productService->isUniqueCodeForProduct('test3', $companyId_1));
        $this->assertTrue($this->productService->isUniqueCodeForProduct('test1', $companyId_2));
    }

    public function test_product_service_call_function_is_unique_code_for_product_unit_expect_can_detect_unique_code()
    {
        $productGroupSeeder = new ProductGroupTableSeeder();
        $brandSeeder = new BrandTableSeeder();
        $unitSeeder = new UnitTableSeeder();
        $productSeeder = new ProductTableSeeder();
        
        $user = User::factory()
                    ->has(Company::factory()->count(2), 'companies')
                    ->create();

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;

        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId_1, ProductGroupCategory::PRODUCTS->value]);
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId_1]);            
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId_1, UnitCategory::PRODUCTS->value]);
        $productSeeder->callWith(ProductTableSeeder::class, [1, $companyId_1, ProductCategory::PRODUCTS->value]);

        $productUnit_company_1 = $company_1->products()->first()->productUnits()->inRandomOrder()->first();
        $productUnit_company_1->code = 'test1';
        $productUnit_company_1->save();

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;

        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId_2, ProductGroupCategory::PRODUCTS->value]);
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId_2]);            
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId_2, UnitCategory::PRODUCTS->value]);
        $productSeeder->callWith(ProductTableSeeder::class, [1, $companyId_2, ProductCategory::PRODUCTS->value]);

        $productUnit_company_2 = $company_2->products()->first()->productUnits()->inRandomOrder()->first();
        $productUnit_company_2->code = 'test2';
        $productUnit_company_2->save();

        $this->assertFalse($this->productService->isUniqueCodeForProductUnits('test1', $companyId_1));
        $this->assertTrue($this->productService->isUniqueCodeForProductUnits('test2', $companyId_1));
        $this->assertTrue($this->productService->isUniqueCodeForProductUnits('test3', $companyId_1));
        $this->assertTrue($this->productService->isUniqueCodeForProductUnits('test1', $companyId_2));
    }
    /* #endregion */
}