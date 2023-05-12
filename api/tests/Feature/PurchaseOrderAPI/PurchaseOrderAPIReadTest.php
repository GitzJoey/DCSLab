<?php

namespace Tests\Feature\PurchaseOrderAPI;

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
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Tests\APITestCase;

class PurchaseOrderAPIReadTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_purchase_order_api_call_read_expect_successful()
    {
        $this->markTestSkipped('Under Constructions');

        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
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
                            ->has(PurchaseOrderDiscount::factory()
                                ->for($company)
                                ->for($branch)
                                ->setGlobalDiscountRandom()
                            );

        $productUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($productUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $purchaseOrder = $purchaseOrder->has(PurchaseOrderProductUnit::factory()
                                ->for($company)->for($branch)
                                ->for($productUnit->product)
                                ->for($productUnit)
            );
        }

        $purchaseOrder = $purchaseOrder->create();

        $api = $this->getJson(route('api.get.db.purchase_order.purchase_order.read', $purchaseOrder->ulid));

        $api->assertSuccessful();
    }

    public function test_purchase_order_api_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $this->markTestSkipped('Under Constructions');
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                ->has(Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                    ->has(Brand::factory()->count(5))
                    ->has(Unit::factory()->setCategoryToProduct()->count(5))
                    ->has(Supplier::factory())
                )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $supplier = $company->suppliers()->inRandomOrder()->first();

        $productSeedCount = random_int(1, 10);
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

        $purchaseOrder = PurchaseOrder::factory()
                            ->for($company)
                            ->for($branch)
                            ->for($supplier);

        $productUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($productUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $purchaseOrder = $purchaseOrder->has(PurchaseOrderProductUnit::factory()
                                ->for($company)->for($branch)
                                ->for($productUnit->product)
                                ->for($productUnit)
            );
        }

        $purchaseOrder->create();

        $result = $this->purchaseOrderActions->readAny(
            companyId: $company->id,
            branchId: $branch->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_purchase_order_api_call_read_any_with_paginate_false_expect_collection_object()
    {
        $this->markTestSkipped('Under Constructions');
        $user = User::factory()
        ->has(Company::factory()->setStatusActive()->setIsDefault()
            ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
            ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
            ->has(Brand::factory()->count(5))
            ->has(Unit::factory()->setCategoryToProduct()->count(5))
            ->has(Supplier::factory())
        )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $supplier = $company->suppliers()->inRandomOrder()->first();

        $productSeedCount = random_int(1, 10);
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

        $purchaseOrder = PurchaseOrder::factory()
                            ->for($company)
                            ->for($branch)
                            ->for($supplier);

        $productUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($productUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $purchaseOrder = $purchaseOrder->has(PurchaseOrderProductUnit::factory()
                                ->for($company)->for($branch)
                                ->for($productUnit->product)
                                ->for($productUnit)
            );
        }

        $purchaseOrder->create();

        $result = $this->purchaseOrderActions->readAny(
            companyId: $company->id,
            branchId: $branch->id,
            search: '',
            paginate: false,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_purchase_order_api_call_read_any_with_nonexistance_company_id_expect_empty_collection()
    {
        $this->markTestSkipped('Under Constructions');
        $maxId = Company::max('id') + 1;
        $result = $this->purchaseOrderActions->readAny(companyId: $maxId, search: '', paginate: false);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_purchase_order_api_call_read_any_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestSkipped('Under Constructions');
        $user = User::factory()
        ->has(Company::factory()->setStatusActive()->setIsDefault()
            ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
            ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
            ->has(Brand::factory()->count(5))
            ->has(Unit::factory()->setCategoryToProduct()->count(5))
            ->has(Supplier::factory())
        )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $supplier = $company->suppliers()->inRandomOrder()->first();

        $productSeedCount = random_int(1, 10);
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

        $purchaseOrder = PurchaseOrder::factory()
                            ->for($company)
                            ->for($branch)
                            ->for($supplier);

        $productUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($productUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $purchaseOrder = $purchaseOrder->has(PurchaseOrderProductUnit::factory()
                                ->for($company)->for($branch)
                                ->for($productUnit->product)
                                ->for($productUnit)
            );
        }

        $purchaseOrder->create();

        $maxId = Branch::max('id') + 1;
        $result = $this->purchaseOrderActions->readAny(
            companyId: $company->id,
            branchId: $maxId,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_purchase_order_api_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $this->markTestSkipped('Under Constructions');
        $user = User::factory()
        ->has(Company::factory()->setStatusActive()->setIsDefault()
            ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
            ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
            ->has(Brand::factory()->count(5))
            ->has(Unit::factory()->setCategoryToProduct()->count(5))
            ->has(Supplier::factory())
        )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $supplier = $company->suppliers()->inRandomOrder()->first();

        $productSeedCount = random_int(1, 10);
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

        for ($i = 0; $i < 3; $i++) {
            $purchaseOrder = PurchaseOrder::factory()
                            ->for($company)
                            ->for($branch)
                            ->for($supplier);

            $productUnitCount = random_int(1, $company->productUnits()->count());
            $productUnits = $company->productUnits()->inRandomOrder()->take($productUnitCount)->get();

            foreach ($productUnits as $productUnit) {
                $purchaseOrder = $purchaseOrder->has(PurchaseOrderProductUnit::factory()
                                    ->for($company)->for($branch)
                                    ->for($productUnit->product)
                                    ->for($productUnit)
                );
            }

            $purchaseOrder->create();
        }

        $result = $this->purchaseOrderActions->readAny(
            companyId: $company->id,
            branchId: $branch->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 3);
    }

    public function test_purchase_order_api_call_read_expect_object()
    {
        $this->markTestSkipped('Under Constructions');
        $user = User::factory()
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                        ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                        ->has(Brand::factory()->count(5))
                        ->has(Unit::factory()->setCategoryToProduct()->count(5))
                        ->has(Supplier::factory())
                    )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $supplier = $company->suppliers()->inRandomOrder()->first();

        $productSeedCount = random_int(1, 10);
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

        $purchaseOrder = PurchaseOrder::factory()
                            ->for($company)
                            ->for($branch)
                            ->for($supplier);

        $productUnitCount = random_int(1, $company->productUnits()->count());
        $productUnits = $company->productUnits()->inRandomOrder()->take($productUnitCount)->get();

        foreach ($productUnits as $productUnit) {
            $purchaseOrder = $purchaseOrder->has(PurchaseOrderProductUnit::factory()
                                ->for($company)->for($branch)
                                ->for($productUnit->product)
                                ->for($productUnit)
            );
        }

        $purchaseOrder = $purchaseOrder->create();

        $result = $this->purchaseOrderActions->read($purchaseOrder);

        $this->assertInstanceOf(PurchaseOrder::class, $result);
    }
}
