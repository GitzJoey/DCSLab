<?php

namespace Tests\Feature\API\PurchaseOrderAPI;

use App\Enums\ProductGroupCategory;
use App\Enums\UnitCategory;
use App\Enums\UserRoles;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductUnit;
use App\Models\Profile;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDiscount;
use App\Models\PurchaseOrderProductUnit;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\Unit;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseOrderAPIReadTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_purchase_order_api_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        for ($i = 0; $i < 3; $i++) {
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
        }

        for ($i = 0; $i < 3; $i++) {
            $supplier = Supplier::factory()
                ->for($company)
                ->for(
                    User::factory()
                        ->has(Profile::factory())
                        ->hasAttached(Role::where('name', '=', UserRoles::USER->value)->first())
                        ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
                        ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
                        ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
                );

            $products = $company->products()
                ->take(random_int(1, $company->products()->count()))
                ->get()->shuffle();

            $mainProductIdx = random_int(0, $products->count() - 1);

            for ($j = 0; $j < $products->count(); $j++) {
                $supplier = $supplier->has(
                    SupplierProduct::factory()->for($company)->for($products[$j])
                        ->setMainProduct($j == $mainProductIdx)
                );
            }

            $supplier->create();
        }

        $supplier = $company->suppliers()->inRandomOrder()->first();

        $purchaseOrder = PurchaseOrder::factory()
            ->for($company)
            ->for($branch)
            ->for($supplier)
            ->has(PurchaseOrderDiscount::factory()->for($company)->for($branch)->setGlobalDiscountRandom());

        $productUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($productUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $purchaseOrder = $purchaseOrder->has(
                PurchaseOrderProductUnit::factory()
                    ->for($company)->for($branch)
                    ->for($productUnit->product)
                    ->for($productUnit)
            );
        }

        $purchaseOrder = $purchaseOrder->create();

        $api = $this->getJson(route('api.get.db.purchase_order.purchase_order.read_any', [
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
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

    public function test_purchase_order_api_call_read_any_with_pagination_expect_several_per_page()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        for ($i = 0; $i < 3; $i++) {
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
        }

        for ($i = 0; $i < 3; $i++) {
            $supplier = Supplier::factory()
                ->for($company)
                ->for(
                    User::factory()
                        ->has(Profile::factory())
                        ->hasAttached(Role::where('name', '=', UserRoles::USER->value)->first())
                        ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
                        ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
                        ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
                );

            $products = $company->products()
                ->take(random_int(1, $company->products()->count()))
                ->get()->shuffle();

            $mainProductIdx = random_int(0, $products->count() - 1);

            for ($j = 0; $j < $products->count(); $j++) {
                $supplier = $supplier->has(
                    SupplierProduct::factory()->for($company)->for($products[$j])
                        ->setMainProduct($j == $mainProductIdx)
                );
            }

            $supplier->create();
        }

        $supplier = $company->suppliers()->inRandomOrder()->first();

        $purchaseOrder = PurchaseOrder::factory()
            ->for($company)
            ->for($branch)
            ->for($supplier)
            ->has(PurchaseOrderDiscount::factory()->for($company)->for($branch)->setGlobalDiscountRandom());

        $productUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($productUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $purchaseOrder = $purchaseOrder->has(
                PurchaseOrderProductUnit::factory()
                    ->for($company)->for($branch)
                    ->for($productUnit->product)
                    ->for($productUnit)
            );
        }

        $purchaseOrder = $purchaseOrder->create();

        $api = $this->getJson(route('api.get.db.purchase_order.purchase_order.read_any', [
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
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

    public function test_purchase_order_api_call_read_any_without_search_querystring_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        for ($i = 0; $i < 3; $i++) {
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
        }

        for ($i = 0; $i < 3; $i++) {
            $supplier = Supplier::factory()
                ->for($company)
                ->for(
                    User::factory()
                        ->has(Profile::factory())
                        ->hasAttached(Role::where('name', '=', UserRoles::USER->value)->first())
                        ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
                        ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
                        ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
                );

            $products = $company->products()
                ->take(random_int(1, $company->products()->count()))
                ->get()->shuffle();

            $mainProductIdx = random_int(0, $products->count() - 1);

            for ($j = 0; $j < $products->count(); $j++) {
                $supplier = $supplier->has(
                    SupplierProduct::factory()->for($company)->for($products[$j])
                        ->setMainProduct($j == $mainProductIdx)
                );
            }

            $supplier->create();
        }

        $supplier = $company->suppliers()->inRandomOrder()->first();

        $purchaseOrder = PurchaseOrder::factory()
            ->for($company)
            ->for($branch)
            ->for($supplier)
            ->has(PurchaseOrderDiscount::factory()->for($company)->for($branch)->setGlobalDiscountRandom());

        $productUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($productUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $purchaseOrder = $purchaseOrder->has(
                PurchaseOrderProductUnit::factory()
                    ->for($company)->for($branch)
                    ->for($productUnit->product)
                    ->for($productUnit)
            );
        }

        $purchaseOrder = $purchaseOrder->create();

        $api = $this->getJson(route('api.get.db.purchase_order.purchase_order.read_any', [
            'company_id' => Hashids::encode($company->id),
        ]));

        $api->assertStatus(422);
    }

    public function test_purchase_order_api_call_read_any_with_special_char_in_search_expect_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        for ($i = 0; $i < 3; $i++) {
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
        }

        for ($i = 0; $i < 3; $i++) {
            $supplier = Supplier::factory()
                ->for($company)
                ->for(
                    User::factory()
                        ->has(Profile::factory())
                        ->hasAttached(Role::where('name', '=', UserRoles::USER->value)->first())
                        ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
                        ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
                        ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
                );

            $products = $company->products()
                ->take(random_int(1, $company->products()->count()))
                ->get()->shuffle();

            $mainProductIdx = random_int(0, $products->count() - 1);

            for ($j = 0; $j < $products->count(); $j++) {
                $supplier = $supplier->has(
                    SupplierProduct::factory()->for($company)->for($products[$j])
                        ->setMainProduct($j == $mainProductIdx)
                );
            }

            $supplier->create();
        }

        $supplier = $company->suppliers()->inRandomOrder()->first();

        $purchaseOrder = PurchaseOrder::factory()
            ->for($company)
            ->for($branch)
            ->for($supplier)
            ->has(PurchaseOrderDiscount::factory()->for($company)->for($branch)->setGlobalDiscountRandom());

        $productUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($productUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $purchaseOrder = $purchaseOrder->has(
                PurchaseOrderProductUnit::factory()
                    ->for($company)->for($branch)
                    ->for($productUnit->product)
                    ->for($productUnit)
            );
        }

        $purchaseOrder = $purchaseOrder->create();

        $api = $this->getJson(route('api.get.db.purchase_order.purchase_order.read_any', [
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
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

    public function test_purchase_order_api_call_read_any_with_negative_value_in_parameters_expect_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        for ($i = 0; $i < 3; $i++) {
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
        }

        for ($i = 0; $i < 3; $i++) {
            $supplier = Supplier::factory()
                ->for($company)
                ->for(
                    User::factory()
                        ->has(Profile::factory())
                        ->hasAttached(Role::where('name', '=', UserRoles::USER->value)->first())
                        ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
                        ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
                        ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
                );

            $products = $company->products()
                ->take(random_int(1, $company->products()->count()))
                ->get()->shuffle();

            $mainProductIdx = random_int(0, $products->count() - 1);

            for ($j = 0; $j < $products->count(); $j++) {
                $supplier = $supplier->has(
                    SupplierProduct::factory()->for($company)->for($products[$j])
                        ->setMainProduct($j == $mainProductIdx)
                );
            }

            $supplier->create();
        }

        $supplier = $company->suppliers()->inRandomOrder()->first();

        $purchaseOrder = PurchaseOrder::factory()
            ->for($company)
            ->for($branch)
            ->for($supplier)
            ->has(PurchaseOrderDiscount::factory()->for($company)->for($branch)->setGlobalDiscountRandom());

        $productUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($productUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $purchaseOrder = $purchaseOrder->has(
                PurchaseOrderProductUnit::factory()
                    ->for($company)->for($branch)
                    ->for($productUnit->product)
                    ->for($productUnit)
            );
        }

        $purchaseOrder = $purchaseOrder->create();

        $api = $this->getJson(route('api.get.db.purchase_order.purchase_order.read_any', [
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
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

    public function test_purchase_order_api_call_read_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        for ($i = 0; $i < 3; $i++) {
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
        }

        for ($i = 0; $i < 3; $i++) {
            $supplier = Supplier::factory()
                ->for($company)
                ->for(
                    User::factory()
                        ->has(Profile::factory())
                        ->hasAttached(Role::where('name', '=', UserRoles::USER->value)->first())
                        ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
                        ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
                        ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
                );

            $products = $company->products()
                ->take(random_int(1, $company->products()->count()))
                ->get()->shuffle();

            $mainProductIdx = random_int(0, $products->count() - 1);

            for ($j = 0; $j < $products->count(); $j++) {
                $supplier = $supplier->has(
                    SupplierProduct::factory()->for($company)->for($products[$j])
                        ->setMainProduct($j == $mainProductIdx)
                );
            }

            $supplier->create();
        }

        $supplier = $company->suppliers()->inRandomOrder()->first();

        $purchaseOrder = PurchaseOrder::factory()
            ->for($company)
            ->for($branch)
            ->for($supplier)
            ->has(PurchaseOrderDiscount::factory()->for($company)->for($branch)->setGlobalDiscountRandom());

        $productUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($productUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $purchaseOrder = $purchaseOrder->has(
                PurchaseOrderProductUnit::factory()
                    ->for($company)->for($branch)
                    ->for($productUnit->product)
                    ->for($productUnit)
            );
        }

        $purchaseOrder = $purchaseOrder->create();

        $api = $this->getJson(route('api.get.db.purchase_order.purchase_order.read', $purchaseOrder->ulid));

        $api->assertSuccessful();
    }
}
