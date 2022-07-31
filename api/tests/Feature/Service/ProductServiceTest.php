<?php

namespace Tests\Feature\Service;

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
use Database\Seeders\UnitTableSeeder;
use Database\Seeders\BrandTableSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\ProductGroupTableSeeder;
use Database\Seeders\ProductTableSeeder;
use Illuminate\Contracts\Pagination\Paginator;

class ProductServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productService = app(ProductService::class);
    }

    /* #region create */
    public function test_product_service_call_create_product_expect_db_has_record()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        ProductGroup::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([1, 3])
        ]);

        Brand::factory()->count(10)->create(['company_id' => $companyId]);
        
        Unit::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([1, 3])
        ]);

        $productGroupId = ProductGroup::where('company_id', '=', $companyId)
        ->whereOr([
            ['category', '=', ProductGroupCategory::PRODUCTS->value],
            ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
        ])->inRandomOrder()->first()->id;

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
                        ->whereOr([
                            ['category', '=', ProductGroupCategory::PRODUCTS->value], 
                            ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
                        ])->inRandomOrder()->first()->id;

            $conversionValue = $i == 0 ? 1 : $this->faker->numberBetween($maxConverionValue + 1, $maxConverionValue + $this->faker->numberBetween(1, 10));
            $isBase = $i == 0 ? true : false;
            $isPrimaryUnit = $i == $primaryUnitIdx ? true : false;

            $productUnitArr = ProductUnit::factory()->make([
                'unit_id' => $unitId,
                'conv_value' => $conversionValue,
                'is_base' => $isBase,
                'is_primary_unit' => $isPrimaryUnit,
            ])->toArray();

            array_push($productUnitsArr, $productUnitArr);

            $maxConverionValue = $productUnitArr['conv_value'];
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
                'conversion_value' => $productUnitsArr[$i]['conv_value'],
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
        
        ProductGroup::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([2, 3])
        ]);
        
        Unit::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([2, 3])
        ]);

        $productGroupId = ProductGroup::where('company_id', '=', $companyId)
        ->whereOr([
            ['category', '=', ProductGroupCategory::SERVICES->value],
            ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
        ])->inRandomOrder()->first()->id;        

        $productArr = Product::factory()->make([
            'company_id' => $companyId,
            'product_group_id' => $productGroupId,
            'product_type' => 4,
        ])->toArray();

        $productUnitsArr = [];

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
    public function test_product_service_call_list_products_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $result = $this->productService->list(
            companyId: $user->companies->first()->id,
            isProduct: true,
            isService: false,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_product_service_call_list_services_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $result = $this->productService->list(
            companyId: $user->companies->first()->id,
            isProduct: false,
            isService: true,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_product_service_call_list_products_and_services_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $result = $this->productService->list(
            companyId: $user->companies->first()->id,
            isProduct: true,
            isService: true,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_product_service_call_list_products_and_services_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $result = $this->productService->list(
            companyId: $user->companies->first()->id,
            isProduct: true,
            isService: true,
            search: '',
            paginate: false,
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_product_service_call_list_products_and_services_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Product::max('id') + 1;

        $result = $this->productService->list(
            companyId: $maxId,
            isProduct: true,
            isService: true,
            search: '',
            paginate: false,
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_product_service_call_list_products_with_search_parameter_expect_filtered_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        ProductGroup::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([1, 3])
        ]);

        Brand::factory()->count(10)->create(['company_id' => $companyId]);
        
        Unit::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([1, 3])
        ]);

        for ($n = 0; $n < 2; $n++) {
            for ($iCount = 0; $iCount < 10; $iCount++) {      
                $productGroupId = ProductGroup::where('company_id', '=', $companyId)
                ->whereOr([
                    ['category', '=', ProductGroupCategory::PRODUCTS->value],
                    ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
                ])->inRandomOrder()->first()->id;
        
                $brandId = Brand::where('company_id', '=', $companyId)->inRandomOrder()->first()->id;
        
                if ($n == 0) {
                    $productArr = Product::factory()->make([
                        'company_id' => $companyId,
                        'product_group_id' => $productGroupId,
                        'brand_id' => $brandId,
                        'product_type' => $this->faker->randomElement([1, 2, 3]),
                    ])->toArray();
                } elseif ($n == 1) {
                    $productArr = Product::factory()->insertStringInName(' testing')->make([
                        'company_id' => $companyId,
                        'product_group_id' => $productGroupId,
                        'brand_id' => $brandId,
                        'product_type' => $this->faker->randomElement([1, 2, 3]),
                    ])->toArray();
                }
        
                $productUnitsArr = [];
                $unitCount = $this->faker->numberBetween(1, 5);
                $primaryUnitIdx = $this->faker->numberBetween(0, $unitCount - 1);
                $maxConverionValue = 1;
                for ($i = 0; $i < $unitCount ; $i++) {
                    $unitId = Unit::where('company_id', '=', $companyId)
                                ->whereOr([
                                    ['category', '=', ProductGroupCategory::PRODUCTS->value], 
                                    ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
                                ])->inRandomOrder()->first()->id;
        
                    $conversionValue = $i == 0 ? 1 : $this->faker->numberBetween($maxConverionValue + 1, $maxConverionValue + 20);
                    $isBase = $i == 0 ? true : false;
                    $isPrimaryUnit = $i == $primaryUnitIdx ? true : false;
        
                    $productUnitArr = ProductUnit::factory()->make([
                        'unit_id' => $unitId,
                        'conv_value' => $conversionValue,
                        'is_base' => $isBase,
                        'is_primary_unit' => $isPrimaryUnit,
                    ])->toArray();
        
                    array_push($productUnitsArr, $productUnitArr);
        
                    $maxConverionValue = $productUnitArr['conv_value'];
                }
        
                $result = $this->productService->create(
                    $productArr,
                    $productUnitsArr
                );
            }
        }

        $result = $this->productService->list(
            companyId: $companyId,
            isProduct: true,
            isService: false,
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 10);
    }

    public function test_product_service_call_list_services_with_search_parameter_expect_filtered_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        ProductGroup::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([2, 3])
        ]);

        Brand::factory()->count(10)->create(['company_id' => $companyId]);
        
        Unit::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([2, 3])
        ]);

        for ($n = 0; $n < 2; $n++) {
            for ($iCount = 0; $iCount < 10; $iCount++) {      
                $productGroupId = ProductGroup::where('company_id', '=', $companyId)
                ->whereOr([
                    ['category', '=', ProductGroupCategory::SERVICES->value],
                    ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
                ])->inRandomOrder()->first()->id;
        
                if ($n == 0) {
                    $productArr = Product::factory()->make([
                        'company_id' => $companyId,
                        'product_group_id' => $productGroupId,
                        'product_type' => 4,
                    ])->toArray();
                } elseif ($n == 1) {
                    $productArr = Product::factory()->insertStringInName(' testing')->make([
                        'company_id' => $companyId,
                        'product_group_id' => $productGroupId,
                        'product_type' => 4,
                    ])->toArray();
                }

                $productUnitsArr = [];
        
                $result = $this->productService->create(
                    $productArr,
                    $productUnitsArr
                );
            }
        }

        $result = $this->productService->list(
            companyId: $companyId,
            isProduct: false,
            isService: true,
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 10);
    }

    public function test_product_service_call_list_products_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        ProductGroup::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([1, 3])
        ]);

        Brand::factory()->count(10)->create(['company_id' => $companyId]);
        
        Unit::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([1, 3])
        ]);

        for ($iCount = 0; $iCount < 10; $iCount++) {
            $productGroupId = ProductGroup::where('company_id', '=', $companyId)
            ->whereOr([
                ['category', '=', ProductGroupCategory::PRODUCTS->value],
                ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
            ])->inRandomOrder()->first()->id;
    
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
                            ->whereOr([
                                ['category', '=', ProductGroupCategory::PRODUCTS->value], 
                                ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
                            ])->inRandomOrder()->first()->id;
    
                $conversionValue = $i == 0 ? 1 : $this->faker->numberBetween($maxConverionValue + 1, $maxConverionValue + 20);
                $isBase = $i == 0 ? true : false;
                $isPrimaryUnit = $i == $primaryUnitIdx ? true : false;
    
                $productUnitArr = ProductUnit::factory()->make([
                    'unit_id' => $unitId,
                    'conv_value' => $conversionValue,
                    'is_base' => $isBase,
                    'is_primary_unit' => $isPrimaryUnit,
                ])->toArray();
    
                array_push($productUnitsArr, $productUnitArr);
    
                $maxConverionValue = $productUnitArr['conv_value'];
            }

            $this->productService->create(
                $productArr,
                $productUnitsArr
            );
        }
        
        $result = $this->productService->list(
            companyId: $companyId, 
            isProduct: true,
            isService: false,
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 10);
    }

    public function test_product_service_call_list_services_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        ProductGroup::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([2, 3])
        ]);

        Brand::factory()->count(10)->create(['company_id' => $companyId]);
        
        Unit::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([2, 3])
        ]);

        for ($iCount = 0; $iCount < 10; $iCount++) {
            $productGroupId = ProductGroup::where('company_id', '=', $companyId)
            ->whereOr([
                ['category', '=', ProductGroupCategory::SERVICES->value],
                ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
            ])->inRandomOrder()->first()->id;
    
            $brandId = Brand::where('company_id', '=', $companyId)->inRandomOrder()->first()->id;
    
            $productArr = Product::factory()->make([
                'company_id' => $companyId,
                'product_group_id' => $productGroupId,
                'brand_id' => $brandId,
                'product_type' => 4,
            ])->toArray();
    
            $productUnitsArr = [];

            $this->productService->create(
                $productArr,
                $productUnitsArr
            );
        }
        
        $result = $this->productService->list(
            companyId: $companyId, 
            isProduct: false,
            isService: true,
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
        
        ProductGroup::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([1, 3])
        ]);

        Brand::factory()->count(10)->create(['company_id' => $companyId]);
        
        Unit::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([1, 3])
        ]);

        for ($iCount = 0; $iCount < 10; $iCount++) {
            $productGroupId = ProductGroup::where('company_id', '=', $companyId)
            ->whereOr([
                ['category', '=', ProductGroupCategory::PRODUCTS->value],
                ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
            ])->inRandomOrder()->first()->id;
    
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
                            ->whereOr([
                                ['category', '=', ProductGroupCategory::PRODUCTS->value], 
                                ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
                            ])->inRandomOrder()->first()->id;
    
                $conversionValue = $i == 0 ? 1 : $this->faker->numberBetween($maxConverionValue + 1, $maxConverionValue + 20);
                $isBase = $i == 0 ? true : false;
                $isPrimaryUnit = $i == $primaryUnitIdx ? true : false;
    
                $productUnitArr = ProductUnit::factory()->make([
                    'unit_id' => $unitId,
                    'conv_value' => $conversionValue,
                    'is_base' => $isBase,
                    'is_primary_unit' => $isPrimaryUnit,
                ])->toArray();
    
                array_push($productUnitsArr, $productUnitArr);
    
                $maxConverionValue = $productUnitArr['conv_value'];
            }

            $this->productService->create(
                $productArr,
                $productUnitsArr
            );
        }
        
        $result = $this->productService->list(
            companyId: $companyId, 
            isProduct: true,
            isService: false,
            search: '',
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 10);
    }

    public function test_product_service_call_list_services_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;
        
        ProductGroup::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([2, 3])
        ]);

        Brand::factory()->count(10)->create(['company_id' => $companyId]);
        
        Unit::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([2, 3])
        ]);

        for ($iCount = 0; $iCount < 10; $iCount++) {
            $productGroupId = ProductGroup::where('company_id', '=', $companyId)
            ->whereOr([
                ['category', '=', ProductGroupCategory::SERVICES->value],
                ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
            ])->inRandomOrder()->first()->id;
    
            $brandId = Brand::where('company_id', '=', $companyId)->inRandomOrder()->first()->id;
    
            $productArr = Product::factory()->make([
                'company_id' => $companyId,
                'product_group_id' => $productGroupId,
                'brand_id' => $brandId,
                'product_type' => 4,
            ])->toArray();
    
            $productUnitsArr = [];

            $this->productService->create(
                $productArr,
                $productUnitsArr
            );
        }
        
        $result = $this->productService->list(
            companyId: $companyId, 
            isProduct: false,
            isService: true,
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

        $companyId = $user->companies->first()->id;
        
        ProductGroup::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([1, 3])
        ]);

        Brand::factory()->count(10)->create(['company_id' => $companyId]);
        
        Unit::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([1, 3])
        ]);

        $productGroupId = ProductGroup::where('company_id', '=', $companyId)
        ->whereOr([
            ['category', '=', ProductGroupCategory::PRODUCTS->value],
            ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
        ])->inRandomOrder()->first()->id;

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
                        ->whereOr([
                            ['category', '=', ProductGroupCategory::PRODUCTS->value], 
                            ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
                        ])->inRandomOrder()->first()->id;

            $conversionValue = $i == 0 ? 1 : $this->faker->numberBetween($maxConverionValue + 1, $maxConverionValue + 20);
            $isBase = $i == 0 ? true : false;
            $isPrimaryUnit = $i == $primaryUnitIdx ? true : false;

            $productUnitArr = ProductUnit::factory()->make([
                'unit_id' => $unitId,
                'conv_value' => $conversionValue,
                'is_base' => $isBase,
                'is_primary_unit' => $isPrimaryUnit,
            ])->toArray();

            array_push($productUnitsArr, $productUnitArr);

            $maxConverionValue = $productUnitArr['conv_value'];
        }

        $product = $this->productService->create(
            $productArr,
            $productUnitsArr
        );

        $result = $this->productService->read($product);

        $this->assertInstanceOf(Product::class, $result);
    }
    /* #endregion */

    /* #region update */
    public function test_product_service_call_product_update_expect_db_updated()
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
        $productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, 1]);

        $product = $company->products()->first();

        $productArr = Product::factory()->setStatusActive()->make()->toArray();
        $productArr['company_id'] = $companyId;
        $productArr['product_group_id'] = ProductGroup::where('company_id', '=', $companyId)
        ->orWhere([
            ['category', '=', ProductGroupCategory::PRODUCTS->value],
            ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
        ])->inRandomOrder()->first()->id;
        $productArr['brand_id'] = Brand::where('company_id', '=', $companyId)->inRandomOrder()->first()->id;
        $productArr['product_type'] = $this->faker->numberBetween(1, 3);

        $productUnitsArr = [];
        $unitCount = $this->faker->numberBetween(1, 5);
        $primaryUnitIdx = $this->faker->numberBetween(0, $unitCount - 1);
        $maxConverionValue = 1;
        for ($i = 0; $i < $unitCount ; $i++) {
            $unitId = Unit::where('company_id', '=', $companyId)
                        ->whereOr([
                            ['category', '=', ProductGroupCategory::PRODUCTS->value], 
                            ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
                        ])->inRandomOrder()->first()->id;

            $conversionValue = $i == 0 ? 1 : $this->faker->numberBetween($maxConverionValue + 1, $maxConverionValue + 20);
            $isBase = $i == 0 ? true : false;
            $isPrimaryUnit = $i == $primaryUnitIdx ? true : false;

            $productUnitArr = ProductUnit::factory()->make([
                'id' => 0,
                'unit_id' => $unitId,
                'conv_value' => $conversionValue,
                'is_base' => $isBase,
                'is_primary_unit' => $isPrimaryUnit,
            ])->toArray();

            array_push($productUnitsArr, $productUnitArr);

            $maxConverionValue = $productUnitArr['conv_value'];
        }

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

        for ($i = 0; $i < $unitCount ; $i++) {
            $this->assertDatabaseHas('product_units', [
                'company_id' => $companyId,
                'product_id' => $product->id,
                'unit_id' => $productUnitsArr[$i]['unit_id'],
                'code' => $productUnitsArr[$i]['code'],
                'is_base' => $productUnitsArr[$i]['is_base'],
                'conversion_value' => $productUnitsArr[$i]['conv_value'],
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

        $companyId = $user->companies->first()->id;
        
        ProductGroup::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([1, 3])
        ]);

        Brand::factory()->count(10)->create(['company_id' => $companyId]);
        
        Unit::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([1, 3])
        ]);

        $productGroupId = ProductGroup::where('company_id', '=', $companyId)
        ->whereOr([
            ['category', '=', ProductGroupCategory::PRODUCTS->value],
            ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
        ])->inRandomOrder()->first()->id;

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
                        ->whereOr([
                            ['category', '=', ProductGroupCategory::PRODUCTS->value], 
                            ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
                        ])->inRandomOrder()->first()->id;

            $conversionValue = $i == 0 ? 1 : $this->faker->numberBetween($maxConverionValue + 1, $maxConverionValue + 20);
            $isBase = $i == 0 ? true : false;
            $isPrimaryUnit = $i == $primaryUnitIdx ? true : false;

            $productUnitArr = ProductUnit::factory()->make([
                'unit_id' => $unitId,
                'conv_value' => $conversionValue,
                'is_base' => $isBase,
                'is_primary_unit' => $isPrimaryUnit,
            ])->toArray();

            array_push($productUnitsArr, $productUnitArr);

            $maxConverionValue = $productUnitArr['conv_value'];
        }

        $product = $this->productService->create(
            $productArr,
            $productUnitsArr
        );
        
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

        $companyId = $user->companies->first()->id;
        
        ProductGroup::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([1, 3])
        ]);

        Brand::factory()->count(10)->create(['company_id' => $companyId]);
        
        Unit::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => $this->faker->randomElement([1, 3])
        ]);

        $productGroupId = ProductGroup::where('company_id', '=', $companyId)
        ->whereOr([
            ['category', '=', ProductGroupCategory::PRODUCTS->value],
            ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
        ])->inRandomOrder()->first()->id;

        $brandId = Brand::where('company_id', '=', $companyId)->inRandomOrder()->first()->id;

        for ($i = 0; $i < 5; $i++) {
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
                            ->whereOr([
                                ['category', '=', ProductGroupCategory::PRODUCTS->value], 
                                ['category', '=', ProductGroupCategory::PRODUCTS_AND_SERVICES->value],
                            ])->inRandomOrder()->first()->id;
    
                $conversionValue = $i == 0 ? 1 : $this->faker->numberBetween($maxConverionValue + 1, $maxConverionValue + 20);
                $isBase = $i == 0 ? true : false;
                $isPrimaryUnit = $i == $primaryUnitIdx ? true : false;
    
                $productUnitArr = ProductUnit::factory()->make([
                    'unit_id' => $unitId,
                    'conv_value' => $conversionValue,
                    'is_base' => $isBase,
                    'is_primary_unit' => $isPrimaryUnit,
                ])->toArray();
    
                array_push($productUnitsArr, $productUnitArr);
    
                $maxConverionValue = $productUnitArr['conv_value'];
            }
    
            $this->productService->create(
                $productArr,
                $productUnitsArr
            );
        }

        $product = $user->companies()->inRandomOrder()->first()->products()->inRandomOrder()->first();

        $result = $this->productService->delete($product);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
        
    }
    /* #endregion */

    /* #region others */

    /* #endregion */
}