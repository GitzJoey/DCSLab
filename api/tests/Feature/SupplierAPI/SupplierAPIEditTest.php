<?php

namespace Tests\Feature;

use App\Enums\ProductGroupCategory;
use App\Enums\UnitCategory;
use App\Enums\UserRoles;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductUnit;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\Unit;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class SupplierAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_supplier_api_call_update_expect_successful()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                        ->has(Brand::factory()->count(5))
                        ->has(Unit::factory()->setCategoryToProduct()->count(5))
                    )->create();

        $this->actingAs($user);

        $company = $user->companies->first();

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

        $products = $company->products()->where('brand_id', '!=', null)
                        ->take(3)->get();

        $supplier = Supplier::factory()
            ->for($company)
            ->for(User::factory())
            ->has(SupplierProduct::factory()->for($company)->for($products[0]))
            ->has(SupplierProduct::factory()->for($company)->for($products[1]))
            ->has(SupplierProduct::factory()->for($company)->for($products[2]))
            ->create();

        $productCount = random_int(1, $company->products()->count());
        $products = $company->products()->where('brand_id', '!=', null)
                        ->take($productCount)->get();

        $arr_product_id = [];
        $arr_main_product_id = [];
        foreach ($products as $product) {
            array_push($arr_product_id, Hashids::encode($product->id));
            if (random_int(0, 1) == 1) {
                array_push($arr_main_product_id, Hashids::encode($product->id));
            }
        }

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $userFactory = User::factory()->make();
        $supplierArr['pic_create_user'] = random_int(0, 1);
        if ($supplierArr['pic_create_user'] == 1) {
            $supplierArr['pic_contact_person_name'] = $userFactory->name;
            $supplierArr['pic_email'] = $userFactory->email;
            $supplierArr['pic_password'] = '123456';
        }

        $supplierArr['arr_product_id'] = $arr_product_id;
        $supplierArr['arr_main_product_id'] = $arr_main_product_id;

        $api = $this->json('POST', route('api.post.db.supplier.supplier.edit', $supplier->ulid), $supplierArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'company_id' => $company->id,
            'code' => $supplierArr['code'],
            'name' => $supplierArr['name'],
            'payment_term_type' => $supplierArr['payment_term_type'],
            'payment_term' => $supplierArr['payment_term'],
            'contact' => $supplierArr['contact'],
            'address' => $supplierArr['address'],
            'city' => $supplierArr['city'],
            'taxable_enterprise' => $supplierArr['taxable_enterprise'],
            'tax_id' => $supplierArr['tax_id'],
            'remarks' => $supplierArr['remarks'],
            'status' => $supplierArr['status'],
        ]);
    }

    public function test_supplier_api_call_update_with_nonexistance_supplier_product_expect_failed()
    {
        $this->markTestSkipped('Under Constructions');
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                        ->has(Brand::factory()->count(5))
                        ->has(Unit::factory()->setCategoryToProduct()->count(10))
                    )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

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

        $productCount = random_int(1, $company->products()->where('brand_id', '!=', null)->count());
        $products = $company->products()->where('brand_id', '!=', null)
                        ->take($productCount)->get();

        $arr_product_id = [];
        $arr_main_product_id = [];
        foreach ($products as $product) {
            array_push($arr_product_id, Hashids::encode($product->id));
            if (random_int(0, 1) == 1) {
                array_push($arr_main_product_id, Hashids::encode($product->id));
            }
        }

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

        $supplier = $supplier->create();

        $supplierProductsCount = random_int(1, $company->products()->count());

        $arr_product_id = [];
        $productIds = $company->products()->inRandomOrder()->take($supplierProductsCount)->pluck('id');
        for ($i = 0; $i < count($productIds); $i++) {
            if ($i == 0) {
                array_push($arr_product_id, Hashids::encode(Product::max('id') + 1));
            } else {
                array_push($arr_product_id, Hashids::encode($productIds[$i]));
            }
        }

        $arr_main_product_id = [];
        foreach ($arr_product_id as $product_id) {
            $isMainProduct = random_int(0, 1);
            if ($isMainProduct == 1) {
                array_push($arr_main_product_id, $product_id);
            }
        }

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $userFactory = User::factory()->make();
        $supplierArr['pic_create_user'] = random_int(0, 1);
        if ($supplierArr['pic_create_user'] == 1) {
            $supplierArr['pic_contact_person_name'] = $userFactory->name;
            $supplierArr['pic_email'] = $userFactory->email;
            $supplierArr['pic_password'] = '123456';
        }

        $supplierArr['arr_product_id'] = $arr_product_id;
        $supplierArr['arr_main_product_id'] = $arr_main_product_id;

        $api = $this->json('POST', route('api.post.db.supplier.supplier.edit', $supplier->ulid), $supplierArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_supplier_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                        ->has(Brand::factory()->count(5))
                        ->has(Unit::factory()->setCategoryToProduct()->count(10))
                    )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        for ($i = 0; $i < 10; $i++) {
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
            $productCount = random_int(1, $company->products()->where('brand_id', '!=', null)->count());
            $products = $company->products()->where('brand_id', '!=', null)
                            ->take($productCount)->get();

            $arr_product_id = [];
            $arr_main_product_id = [];
            foreach ($products as $product) {
                array_push($arr_product_id, Hashids::encode($product->id));
                if (random_int(0, 1) == 1) {
                    array_push($arr_main_product_id, Hashids::encode($product->id));
                }
            }

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

            $supplier = $supplier->create();
        }

        $supplier = $company->suppliers()->inRandomOrder()->first();

        $supplierProductsCount = random_int(1, $company->products()->count());

        $arr_product_id = [];
        $productIds = $company->products()->inRandomOrder()->take($supplierProductsCount)->pluck('id');
        for ($i = 0; $i < count($productIds); $i++) {
            if ($i == 0) {
                array_push($arr_product_id, Hashids::encode(Product::max('id') + 1));
            } else {
                array_push($arr_product_id, Hashids::encode($productIds[$i]));
            }
        }

        $arr_main_product_id = [];
        foreach ($arr_product_id as $product_id) {
            $isMainProduct = random_int(0, 1);
            if ($isMainProduct == 1) {
                array_push($arr_main_product_id, $product_id);
            }
        }

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $company->suppliers()->where('id', '!=', $supplier->id)->first()->code,
        ])->toArray();

        $userFactory = User::factory()->make();
        $supplierArr['pic_create_user'] = random_int(0, 1);
        if ($supplierArr['pic_create_user'] == 1) {
            $supplierArr['pic_contact_person_name'] = $userFactory->name;
            $supplierArr['pic_email'] = $userFactory->email;
            $supplierArr['pic_password'] = '123456';
        }

        $supplierArr['arr_product_id'] = $arr_product_id;
        $supplierArr['arr_main_product_id'] = $arr_main_product_id;

        $api = $this->json('POST', route('api.post.db.supplier.supplier.edit', $supplier->ulid), $supplierArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_supplier_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                        ->has(Brand::factory()->count(5))
                        ->has(Unit::factory()->setCategoryToProduct()->count(5)))
                    ->has(Company::factory()->setStatusActive()
                        ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                        ->has(Brand::factory()->count(5))
                        ->has(Unit::factory()->setCategoryToProduct()->count(5)))
                    ->create();

        $this->actingAs($user);

        $company_1 = $user->companies[0];

        for ($i = 0; $i < 10; $i++) {
            $productGroup = $company_1->productGroups()
                                ->where('category', '=', ProductGroupCategory::PRODUCTS->value)
                                ->inRandomOrder()->first();

            $brand = $company_1->brands()->inRandomOrder()->first();

            $product = Product::factory()
                        ->for($company_1)
                        ->for($productGroup)
                        ->for($brand)
                        ->setProductTypeAsProduct();

            $units = $company_1->units()->where('category', '=', UnitCategory::PRODUCTS->value)
                        ->inRandomOrder()->get()->shuffle();

            $productUnitCount = random_int(1, $units->count());
            $primaryUnitIdx = random_int(0, $productUnitCount - 1);

            for ($j = 0; $j < $productUnitCount; $j++) {
                $product = $product->has(
                    ProductUnit::factory()
                        ->for($company_1)->for($units[$j])
                        ->setConversionValue($j == 0 ? 1 : random_int(2, 10))
                        ->setIsPrimaryUnit($j == $primaryUnitIdx)
                );
            }

            $product = $product->create();
        }

        for ($i = 0; $i < 3; $i++) {
            $productCount = random_int(1, $company_1->products()->where('brand_id', '!=', null)->count());
            $products = $company_1->products()->where('brand_id', '!=', null)
                            ->take($productCount)->get();

            $arr_product_id = [];
            $arr_main_product_id = [];
            foreach ($products as $product) {
                array_push($arr_product_id, Hashids::encode($product->id));
                if (random_int(0, 1) == 1) {
                    array_push($arr_main_product_id, Hashids::encode($product->id));
                }
            }

            $supplier = Supplier::factory()
                            ->for($company_1)
                            ->for(
                                User::factory()
                                    ->has(Profile::factory())
                                    ->hasAttached(Role::where('name', '=', UserRoles::USER->value)->first())
                                    ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
                                    ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
                                    ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
                            );

            $products = $company_1->products()
                            ->take(random_int(1, $company_1->products()->count()))
                            ->get()->shuffle();

            $mainProductIdx = random_int(0, $products->count() - 1);

            for ($j = 0; $j < $products->count(); $j++) {
                $supplier = $supplier->has(
                    SupplierProduct::factory()->for($company_1)->for($products[$j])
                        ->setMainProduct($j == $mainProductIdx)
                );
            }

            $supplier = $supplier->create();
        }

        $company_2 = $user->companies[1];

        for ($i = 0; $i < 10; $i++) {
            $productGroup = $company_2->productGroups()
                                ->where('category', '=', ProductGroupCategory::PRODUCTS->value)
                                ->inRandomOrder()->first();

            $brand = $company_2->brands()->inRandomOrder()->first();

            $product = Product::factory()
                        ->for($company_2)
                        ->for($productGroup)
                        ->for($brand)
                        ->setProductTypeAsProduct();

            $units = $company_2->units()->where('category', '=', UnitCategory::PRODUCTS->value)
                        ->inRandomOrder()->get()->shuffle();

            $productUnitCount = random_int(1, $units->count());
            $primaryUnitIdx = random_int(0, $productUnitCount - 1);

            for ($j = 0; $j < $productUnitCount; $j++) {
                $product = $product->has(
                    ProductUnit::factory()
                        ->for($company_2)->for($units[$j])
                        ->setConversionValue($j == 0 ? 1 : random_int(2, 10))
                        ->setIsPrimaryUnit($j == $primaryUnitIdx)
                );
            }

            $product = $product->create();
        }

        for ($i = 0; $i < 3; $i++) {
            $productCount = random_int(1, $company_2->products()->where('brand_id', '!=', null)->count());
            $products = $company_2->products()->where('brand_id', '!=', null)
                            ->take($productCount)->get();

            $arr_product_id = [];
            $arr_main_product_id = [];
            foreach ($products as $product) {
                array_push($arr_product_id, Hashids::encode($product->id));
                if (random_int(0, 1) == 1) {
                    array_push($arr_main_product_id, Hashids::encode($product->id));
                }
            }

            $supplier = Supplier::factory()
                            ->for($company_2)
                            ->for(
                                User::factory()
                                    ->has(Profile::factory())
                                    ->hasAttached(Role::where('name', '=', UserRoles::USER->value)->first())
                                    ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
                                    ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
                                    ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
                            );

            $products = $company_2->products()
                            ->take(random_int(1, $company_2->products()->count()))
                            ->get()->shuffle();

            $mainProductIdx = random_int(0, $products->count() - 1);

            for ($j = 0; $j < $products->count(); $j++) {
                $supplier = $supplier->has(
                    SupplierProduct::factory()->for($company_2)->for($products[$j])
                        ->setMainProduct($j == $mainProductIdx)
                );
            }

            $supplier = $supplier->create();
        }

        $supplier = $company_1->suppliers()->inRandomOrder()->first();

        $supplierProductsCount = random_int(1, $company_1->products()->count());

        $arr_product_id = [];
        $productIds = $company_1->products()->inRandomOrder()->take($supplierProductsCount)->pluck('id');
        for ($i = 0; $i < count($productIds); $i++) {
            array_push($arr_product_id, Hashids::encode($productIds[$i]));
        }

        $arr_main_product_id = [];
        foreach ($arr_product_id as $product_id) {
            $isMainProduct = random_int(0, 1);
            if ($isMainProduct == 1) {
                array_push($arr_main_product_id, $product_id);
            }
        }

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($company_1->id),
            'code' => $company_2->suppliers()->inRandomOrder()->first()->code,
        ])->toArray();

        $userFactory = User::factory()->make();
        $supplierArr['pic_create_user'] = random_int(0, 1);
        if ($supplierArr['pic_create_user'] == 1) {
            $supplierArr['pic_contact_person_name'] = $userFactory->name;
            $supplierArr['pic_email'] = $userFactory->email;
            $supplierArr['pic_password'] = '123456';
        }

        $supplierArr['arr_product_id'] = $arr_product_id;
        $supplierArr['arr_main_product_id'] = $arr_main_product_id;

        $api = $this->json('POST', route('api.post.db.supplier.supplier.edit', $supplier->ulid), $supplierArr);

        $api->assertSuccessful();
    }
}
