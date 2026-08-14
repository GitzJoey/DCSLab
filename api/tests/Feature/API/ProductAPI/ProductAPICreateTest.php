<?php

namespace Tests\Feature\API\ProductAPI;

use App\Enums\UserRolesEnum;
use App\Helpers\HashidsHelper;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class ProductAPICreateTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_product_api_call_store_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductCategory::factory()->count(3))
                ->has(Brand::factory()->count(3)))
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productCategory = $company->productCategories()->inRandomOrder()->first();
        $brand = $company->brands()->inRandomOrder()->first();

        $productArr = Product::factory()->make([
            'product_category_id' => Hashids::encode($productCategory->id),
            'brand_id' => Hashids::encode($brand->id),
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertUnauthorized();
    }

    public function test_product_api_call_store_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductCategory::factory()->count(3))
                ->has(Brand::factory()->count(3)))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $productCategory = $company->productCategories()->inRandomOrder()->first();
        $brand = $company->brands()->inRandomOrder()->first();

        $productArr = Product::factory()->make([
            'product_category_id' => Hashids::encode($productCategory->id),
            'brand_id' => Hashids::encode($brand->id),
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertForbidden();
    }

    public function test_product_api_call_store_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_product_api_call_store_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_product_api_call_store_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductCategory::factory()->count(3))
                ->has(Brand::factory()->count(3)))
            ->create();

        $this->actingAs($user);

        $productArr = (new ProductSeeder)->makeProductUnits(encode: true)->toArray();

        // $company = $user->companies()->inRandomOrder()->first();
        // $productCategory = $company->productCategories()->inRandomOrder()->first();
        // $brand = $company->brands()->inRandomOrder()->first();

        // $productArr = Product::factory()->make([
        //     'product_category_id' => Hashids::encode($productCategory->id),
        //     'brand_id' => Hashids::encode($brand->id),
        //     'company_id' => Hashids::encode($company->id),
        // ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertSuccessful();

        $productArr['product_unit_id'] = HashidsHelper::decodeId($api['data']['product_unit']['id']);

        $this->assertDatabaseHas('products', [
            'product_category_id' => Hashids::decode($productArr['product_category_id'])[0],
            'brand_id' => Hashids::decode($productArr['brand_id'])[0],
            'company_id' => Hashids::decode($productArr['company_id'])[0],
            'code' => $productArr['code'],
            'name' => $productArr['name'],
            'product_type' => $productArr['product_type'],
            'taxable_supply' => $productArr['taxable_supply'],
            'standard_rated_supply' => $productArr['standard_rated_supply'],
            'price_include_vat' => $productArr['price_include_vat'],
            'point' => $productArr['point'],
            'use_serial_number' => $productArr['use_serial_number'],
            'has_expiry_date' => $productArr['has_expiry_date'],
            'status' => $productArr['status'],
            'remarks' => $productArr['remarks'],
        ]);
    }

    // public function test_product_api_call_store_expect_successful()
    // {
    //     $user = User::factory()
    //         ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
    //         ->has(Company::factory()->setStatusActive()->setIsDefault()
    //             ->has(ProductCategory::factory()->count(3))
    //             ->has(Brand::factory()->count(3)))
    //         ->create();

    //     $this->actingAs($user);

    //     $productArr = (new ProductSeeder())->make(encode: true)->toArray();

    //     $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

    //     $api->assertSuccessful();

    //     $productArr['product_unit_id'] = HashidsHelper::decodeId($api['data']['product_unit']['id']);

    //     $this->assertDatabaseHas('products', [
    //         'product_category_id' => Hashids::decode($productArr['product_category_id'])[0],
    //         'brand_id' => Hashids::decode($productArr['brand_id'])[0],
    //         'company_id' => Hashids::decode($productArr['company_id'])[0],
    //         'code' => $productArr['code'],
    //         'name' => $productArr['name'],
    //         'product_type' => $productArr['product_type'],
    //         'taxable_supply' => $productArr['taxable_supply'],
    //         'standard_rated_supply' => $productArr['standard_rated_supply'],
    //         'price_include_vat' => $productArr['price_include_vat'],
    //         'point' => $productArr['point'],
    //         'use_serial_number' => $productArr['use_serial_number'],
    //         'has_expiry_date' => $productArr['has_expiry_date'],
    //         'status' => $productArr['status'],
    //         'remarks' => $productArr['remarks'],
    //     ]);
    // }

    public function test_product_api_call_store_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_product_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        Product::factory()->for($company)->create([
            'code' => 'test1',
        ]);

        $productArr = Product::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_store_with_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->take(2)->get();

        $company_1 = $companies[0];

        $company_2 = $companies[1];

        Product::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $productArr = Product::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('products', [
            'company_id' => $company_2->id,
            'code' => $productArr['code'],
            'name' => $productArr['name'],
        ]);
    }

    public function test_product_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $productArr = [];

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertJsonValidationErrors(['company_id', 'code', 'name']);
    }
}
