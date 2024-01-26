<?php

namespace Tests\Feature\API\SupplierAPI;

use App\Enums\ProductGroupCategory;
use App\Enums\UnitCategory;
use App\Enums\UserRoles;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductUnit;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class SupplierAPICreateTest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_supplier_api_call_store_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                ->has(Brand::factory()->count(5))
                ->has(Unit::factory()->setCategoryToProduct()->count(5))
            )->create();

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

        $arr_supplier_product_product_id = [];
        $arr_supplier_product_main_product_id = [];
        foreach ($products as $product) {
            array_push($arr_supplier_product_product_id, Hashids::encode($product->id));
            if (random_int(0, 1) == 1) {
                array_push($arr_supplier_product_main_product_id, Hashids::encode($product->id));
            }
        }

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $supplierArr['pic_create_user'] = random_int(0, 1);
        if ($supplierArr['pic_create_user'] == 1) {
            $supplierArr['pic_contact_person_name'] = $this->faker->name();
            $supplierArr['pic_email'] = $this->faker->email();
            $supplierArr['pic_password'] = '123456';
        }

        $supplierArr['arr_supplier_product_product_id'] = $arr_supplier_product_product_id;
        $supplierArr['arr_supplier_product_main_product_id'] = $arr_supplier_product_main_product_id;

        $api = $this->json('POST', route('api.post.db.supplier.supplier.save'), $supplierArr);

        $api->assertStatus(401);
    }

    public function test_supplier_api_call_store_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                ->has(Brand::factory()->count(5))
                ->has(Unit::factory()->setCategoryToProduct()->count(5))
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

        $arr_supplier_product_product_id = [];
        $arr_supplier_product_main_product_id = [];
        foreach ($products as $product) {
            array_push($arr_supplier_product_product_id, Hashids::encode($product->id));
            if (random_int(0, 1) == 1) {
                array_push($arr_supplier_product_main_product_id, Hashids::encode($product->id));
            }
        }

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $supplierArr['pic_create_user'] = random_int(0, 1);
        if ($supplierArr['pic_create_user'] == 1) {
            $supplierArr['pic_contact_person_name'] = $this->faker->name();
            $supplierArr['pic_email'] = $this->faker->email();
            $supplierArr['pic_password'] = '123456';
        }

        $supplierArr['arr_supplier_product_product_id'] = $arr_supplier_product_product_id;
        $supplierArr['arr_supplier_product_main_product_id'] = $arr_supplier_product_main_product_id;

        $api = $this->json('POST', route('api.post.db.supplier.supplier.save'), $supplierArr);

        $api->assertStatus(403);
    }

    public function test_supplier_api_call_store_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_supplier_api_call_store_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_supplier_api_call_store_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                ->has(Brand::factory()->count(5))
                ->has(Unit::factory()->setCategoryToProduct()->count(5))
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

        $arr_supplier_product_product_id = [];
        $arr_supplier_product_main_product_id = [];
        foreach ($products as $product) {
            array_push($arr_supplier_product_product_id, Hashids::encode($product->id));
            if (random_int(0, 1) == 1) {
                array_push($arr_supplier_product_main_product_id, Hashids::encode($product->id));
            }
        }

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $supplierArr['pic_create_user'] = random_int(0, 1);
        if ($supplierArr['pic_create_user'] == 1) {
            $supplierArr['pic_contact_person_name'] = $this->faker->name();
            $supplierArr['pic_email'] = $this->faker->email();
            $supplierArr['pic_password'] = '123456';
        }

        $supplierArr['arr_supplier_product_product_id'] = $arr_supplier_product_product_id;
        $supplierArr['arr_supplier_product_main_product_id'] = $arr_supplier_product_main_product_id;

        $api = $this->json('POST', route('api.post.db.supplier.supplier.save'), $supplierArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('suppliers', [
            'company_id' => Hashids::decode($supplierArr['company_id'])[0],
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

    public function test_supplier_api_call_store_with_nonexistance_supplier_product_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                ->has(Brand::factory()->count(5))
                ->has(Unit::factory()->setCategoryToProduct()->count(10))
            )->create();

        $this->actingAs($user);

        $company = $user->companies->first();

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

            $product->create();
        }

        $supplierProductsCount = random_int(1, $company->products()->count());

        $arr_supplier_product_product_id = [];
        $productIds = $company->products()->inRandomOrder()->take($supplierProductsCount)->pluck('id');
        for ($i = 0; $i < count($productIds); $i++) {
            if ($i == 0) {
                array_push($arr_supplier_product_product_id, Hashids::encode(Product::max('id') + 1));
            } else {
                array_push($arr_supplier_product_product_id, Hashids::encode($productIds[$i]));
            }
        }

        $arr_supplier_product_main_product_id = [];
        foreach ($arr_supplier_product_product_id as $product_id) {
            $isMainProduct = random_int(0, 1);
            if ($isMainProduct == 1) {
                array_push($arr_supplier_product_main_product_id, $product_id);
            }
        }

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $supplierArr['pic_create_user'] = random_int(0, 1);
        if ($supplierArr['pic_create_user'] == 1) {
            $supplierArr['pic_contact_person_name'] = $this->faker->name();
            $supplierArr['pic_email'] = $this->faker->email();
            $supplierArr['pic_password'] = '123456';
        }

        $supplierArr['arr_supplier_product_product_id'] = $arr_supplier_product_product_id;
        $supplierArr['arr_supplier_product_main_product_id'] = $arr_supplier_product_main_product_id;

        $api = $this->json('POST', route('api.post.db.supplier.supplier.save'), $supplierArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_supplier_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault()
                ->has(Supplier::factory())
            )->create();

        $this->actingAs($user);

        $company = $user->companies->first();

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $company->suppliers()->inRandomOrder()->first()->code,
        ])->toArray();

        $supplierArr['pic_create_user'] = random_int(0, 1);
        if ($supplierArr['pic_create_user'] == 1) {
            $supplierArr['pic_contact_person_name'] = $this->faker->name();
            $supplierArr['pic_email'] = $this->faker->email();
            $supplierArr['pic_password'] = '123456';
        }

        $supplierArr['arr_supplier_product_product_id'] = [];
        $supplierArr['arr_supplier_product_main_product_id'] = [];

        $api = $this->json('POST', route('api.post.db.supplier.supplier.save'), $supplierArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_supplier_api_call_store_with_empty_string_parameters_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $supplierArr = [];
        [];
        [];
        $api = $this->json('POST', route('api.post.db.supplier.supplier.save'), $supplierArr);

        $api->assertStatus(422);
    }
}
