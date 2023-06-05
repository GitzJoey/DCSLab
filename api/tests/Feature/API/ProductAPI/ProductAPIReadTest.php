<?php

namespace Tests\Feature\API\ProductAPI;

use App\Enums\ProductGroupCategory;
use App\Enums\RecordStatus;
use App\Enums\UnitCategory;
use App\Enums\UserRoles;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductUnit;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class ProductAPIReadTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_product_api_call_read_any_with_or_without_pagination_expect_paginator_or_collection()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()
            ->where('category', '=', ProductGroupCategory::PRODUCTS->value)
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

        $api = $this->getJson(route('api.get.db.product.product.read_any', [
            'company_id' => Hashids::encode($company->id),
            'product_category' => 'PRODUCTS',
            'search' => '',
            'paginate' => true,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);

        $api = $this->getJson(route('api.get.db.product.service.read_any', [
            'company_id' => Hashids::encode($company->id),
            'product_category' => 'SERVICES',
            'search' => '',
            'paginate' => true,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }

    public function test_product_api_call_read_any_with_pagination_expect_several_per_page()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()
            ->where('category', '=', ProductGroupCategory::PRODUCTS->value)
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

        $api = $this->getJson(route('api.get.db.product.product.read_any', [
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'paginate' => true,
            'page' => 1,
            'per_page' => 25,
            'refresh' => true,
        ]));

        $api->assertSuccessful();

        $api->assertJsonFragment([
            'per_page' => 25,
        ]);

        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }

    public function test_product_api_call_read_any_product_with_search_expect_filtered_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $this->actingAs($user);

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

        $api = $this->getJson(route('api.get.db.product.product.read_any', [
            'company_id' => Hashids::encode($company->id),
            'product_category' => 'PRODUCTS',
            'search' => 'testing',
            'paginate' => true,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);

        $api->assertJsonFragment([
            'total' => 3,
        ]);
    }

    public function test_product_api_call_read_any_service_with_search_expect_filtered_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToService()->count(5))
                    ->has(Unit::factory()->setCategoryToService()->count(5))
            )->create();

        $this->actingAs($user);

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

        $api = $this->getJson(route('api.get.db.product.service.read_any', [
            'company_id' => Hashids::encode($company->id),
            'product_category' => 'SERVICES',
            'search' => 'testing',
            'paginate' => true,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);

        $api->assertJsonFragment([
            'total' => 3,
        ]);
    }

    public function test_product_api_call_read_any_with_special_char_in_search_expect_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()
            ->where('category', '=', ProductGroupCategory::PRODUCTS->value)
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

        $api = $this->getJson(route('api.get.db.product.product.read_any', [
            'company_id' => Hashids::encode($company->id),
            'product_category' => 'PRODUCTS',
            'search' => " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~",
            'paginate' => true,
            'page' => 1,
            'per_page' => 10,
            'refresh' => false,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }

    public function test_product_api_call_read_any_with_negative_value_in_parameters_expect_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()
            ->where('category', '=', ProductGroupCategory::PRODUCTS->value)
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

        $api = $this->getJson(route('api.get.db.product.product.read_any', [
            'company_id' => Hashids::encode($company->id),
            'product_category' => 'PRODUCTS',
            'search' => '',
            'paginate' => true,
            'page' => -1,
            'per_page' => -10,
            'refresh' => false,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }

    public function test_product_api_call_read_product_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()
            ->where('category', '=', ProductGroupCategory::PRODUCTS->value)
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

        $api = $this->getJson(route('api.get.db.product.product.read', $product->ulid));

        $api->assertSuccessful();
    }

    public function test_product_api_call_read_service_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToService()->count(5))
                    ->has(Unit::factory()->setCategoryToService()->count(5))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()
            ->where('category', '=', ProductGroupCategory::SERVICES->value)
            ->inRandomOrder()->first();

        $service = Product::factory()
            ->for($company)
            ->for($productGroup)
            ->setProductTypeAsService();

        $unit = $company->units()->where('category', '=', UnitCategory::SERVICES->value)
            ->inRandomOrder()->first();

        $service = $service->has(
            ProductUnit::factory()
                ->for($company)->for($unit)
                ->setConversionValue(1)
                ->setIsPrimaryUnit(true)
        );

        $service = $service->create();

        $api = $this->getJson(route('api.get.db.product.service.read', $service->ulid));

        $api->assertSuccessful();
    }

    public function test_product_api_call_read_without_ulid_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $this->actingAs($user);

        $this->getJson(route('api.get.db.product.product.read', null));
    }

    public function test_product_api_call_read_with_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $this->actingAs($user);

        $ulid = Str::ulid()->generate();

        $api = $this->getJson(route('api.get.db.product.product.read', $ulid));

        $api->assertStatus(404);
    }

    public function test_product_api_call_get_all_active_product_expect_found_active_product()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(10))
                    ->has(Brand::factory()->count(10))
                    ->has(Unit::factory()->setCategoryToProduct()->count(10))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        for ($i = 0; $i < 5; $i++) {
            $productGroup = $company->productGroups()
                ->where('category', '=', ProductGroupCategory::PRODUCTS->value)
                ->inRandomOrder()->first();

            $brand = $company->brands()->inRandomOrder()->first();

            $product = Product::factory()
                ->for($company)
                ->for($productGroup)
                ->for($brand)
                ->setProductTypeAsProduct();

            if ($i == 0) {
                $product = $product->setStatusActive();
            }

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

        $api = $this->getJson(route('api.get.db.product.common.read.product.all.active', [
            'company_id' => Hashids::encode($company->id),
        ]));

        $api->assertSuccessful();

        $countActiveProducts = $company->products()->where('status', '=', RecordStatus::ACTIVE->value)->count();
        $api->assertJsonCount($countActiveProducts, $key = null);

        $resultArr = json_decode($api->getContent(), true);
        foreach ($resultArr as $result) {
            $this->assertTrue($result['status'] == RecordStatus::ACTIVE->name);
        }
    }
}
