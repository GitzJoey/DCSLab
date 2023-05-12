<?php

namespace Tests\Unit\ProductActions;

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
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;

use Tests\ActionsTestCase;

class ProductActionsReadTest extends ActionsTestCase
{
    private ProductActions $productActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productActions = new ProductActions();
    }

    public function test_product_actions_call_read_any_product_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productSeedCount = random_int(1, 5);
        for ($i = 0; $i < $productSeedCount; $i++) {
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

            $product->create();
        }

        $result = $this->productActions->readAny(
            companyId: $company->id,
            productCategory: ProductCategory::PRODUCTS->value,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_product_actions_call_read_any_service_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToService()->count(5))
                    ->has(Unit::factory()->setCategoryToService()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $serviceSeedCount = random_int(1, 5);
        for ($i = 0; $i < $serviceSeedCount; $i++) {
            $productGroup = $company->productGroups()->where('category', '=', ProductGroupCategory::SERVICES->value)
                ->inRandomOrder()->first();

            $unit = $company->units()->where('category', '=', UnitCategory::SERVICES->value)
                ->inRandomOrder()->first();

            Product::factory()
                ->for($company)
                ->for($productGroup)
                ->has(ProductUnit::factory()->for($company)->for($unit)->setConversionValue(1)->setIsPrimaryUnit(true))
                ->setProductTypeAsService()->create();
        }

        $result = $this->productActions->readAny(
            companyId: $company->id,
            productCategory: ProductCategory::SERVICES->value,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_product_actions_call_read_any_product_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productSeedCount = random_int(1, 5);
        for ($i = 0; $i < $productSeedCount; $i++) {
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

            $product->create();
        }

        $result = $this->productActions->readAny(
            companyId: $company->id,
            productCategory: ProductCategory::PRODUCTS->value,
            search: '',
            paginate: false,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_product_actions_call_read_any_service_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToService()->count(5))
                    ->has(Unit::factory()->setCategoryToService()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $serviceSeedCount = random_int(1, 5);
        for ($i = 0; $i < $serviceSeedCount; $i++) {
            $productGroup = $company->productGroups()->where('category', '=', ProductGroupCategory::SERVICES->value)
                ->inRandomOrder()->first();

            $unit = $company->units()->where('category', '=', UnitCategory::SERVICES->value)
                ->inRandomOrder()->first();

            Product::factory()
                ->for($company)
                ->for($productGroup)
                ->has(ProductUnit::factory()->for($company)->for($unit)->setConversionValue(1)->setIsPrimaryUnit(true))
                ->setProductTypeAsService()->create();
        }

        $result = $this->productActions->readAny(
            companyId: $company->id,
            productCategory: ProductCategory::SERVICES->value,
            search: '',
            paginate: false,
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_product_actions_call_read_any_with_nonexistance_company_id_expect_empty_collection()
    {
        $this->markTestSkipped('Under Constructions');
        $maxId = Company::max('id') + 1;

        $result = $this->productActions->readAny(
            companyId: $maxId,
            productCategory: Product::factory()->make()->category->value,
            search: '',
            paginate: false,
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_product_actions_call_read_any_product_with_search_parameter_expect_filtered_results()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        for ($i = 0; $i < 2; $i++) {
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

            $product->create();
        }

        for ($i = 0; $i < 3; $i++) {
            $productGroup = $company->productGroups()->where('category', '=', ProductGroupCategory::PRODUCTS->value)
                ->inRandomOrder()->first();

            $brand = $company->brands()->inRandomOrder()->first();

            $product = Product::factory()
                ->for($company)
                ->for($productGroup)
                ->for($brand)
                ->insertStringInName('testing')
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

            $product->create();
        }

        $result = $this->productActions->readAny(
            companyId: $company->id,
            productCategory: ProductCategory::PRODUCTS->value,
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 3);
    }

    public function test_product_actions_call_read_any_service_with_search_parameter_expect_filtered_results()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToService()->count(5))
                    ->has(Unit::factory()->setCategoryToService()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        for ($i = 0; $i < 2; $i++) {
            $productGroup = $company->productGroups()->where('category', '=', ProductGroupCategory::SERVICES->value)
                ->inRandomOrder()->first();

            $unit = $company->units()->where('category', '=', UnitCategory::SERVICES->value)
                ->inRandomOrder()->first();

            Product::factory()
                ->for($company)
                ->for($productGroup)
                ->has(ProductUnit::factory()->for($company)->for($unit)->setConversionValue(1)->setIsPrimaryUnit(true))
                ->setProductTypeAsService()->create();
        }

        for ($i = 0; $i < 3; $i++) {
            $productGroup = $company->productGroups()->where('category', '=', ProductGroupCategory::SERVICES->value)
                ->inRandomOrder()->first();

            $unit = $company->units()->where('category', '=', UnitCategory::SERVICES->value)
                ->inRandomOrder()->first();

            Product::factory()
                ->for($company)
                ->for($productGroup)
                ->has(ProductUnit::factory()->for($company)->for($unit)->setConversionValue(1)->setIsPrimaryUnit(true))
                ->insertStringInName('testing')
                ->setProductTypeAsService()->create();
        }

        $result = $this->productActions->readAny(
            companyId: $company->id,
            productCategory: ProductCategory::SERVICES->value,
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 3);
    }

    public function test_product_actions_call_read_any_product_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        for ($i = 0; $i < 5; $i++) {
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

            $product->create();
        }

        $result = $this->productActions->readAny(
            companyId: $company->id,
            productCategory: ProductCategory::PRODUCTS->value,
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 5);
    }

    public function test_product_actions_call_read_any_service_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToService()->count(5))
                    ->has(Unit::factory()->setCategoryToService()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        for ($i = 0; $i < 3; $i++) {
            $productGroup = $company->productGroups()->where('category', '=', ProductGroupCategory::SERVICES->value)
                ->inRandomOrder()->first();

            $unit = $company->units()->where('category', '=', UnitCategory::SERVICES->value)
                ->inRandomOrder()->first();

            Product::factory()
                ->for($company)
                ->for($productGroup)
                ->has(ProductUnit::factory()->for($company)->for($unit)->setConversionValue(1)->setIsPrimaryUnit(true))
                ->setProductTypeAsService()->create();
        }

        $result = $this->productActions->readAny(
            companyId: $company->id,
            productCategory: ProductCategory::SERVICES->value,
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 3);
    }

    public function test_product_actions_call_read_any_product_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        for ($i = 0; $i < 3; $i++) {
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

            $product->create();
        }

        $result = $this->productActions->readAny(
            companyId: $company->id,
            productCategory: ProductCategory::PRODUCTS->value,
            search: '',
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 3);
    }

    public function test_product_actions_call_read_any_service_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToService()->count(5))
                    ->has(Unit::factory()->setCategoryToService()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        for ($i = 0; $i < 3; $i++) {
            $productGroup = $company->productGroups()->where('category', '=', ProductGroupCategory::SERVICES->value)
                ->inRandomOrder()->first();

            $unit = $company->units()->where('category', '=', UnitCategory::SERVICES->value)
                ->inRandomOrder()->first();

            Product::factory()
                ->for($company)
                ->for($productGroup)
                ->has(ProductUnit::factory()->for($company)->for($unit)->setConversionValue(1)->setIsPrimaryUnit(true))
                ->setProductTypeAsService()->create();
        }

        $result = $this->productActions->readAny(
            companyId: $company->id,
            productCategory: ProductCategory::SERVICES->value,
            search: '',
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 3);
    }

    public function test_product_actions_call_read_expect_object()
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

        $result = $this->productActions->read($product);

        $this->assertInstanceOf(Product::class, $result);
    }
}
