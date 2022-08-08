<?php

namespace Tests\Feature\API;

use Exception;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Tests\APITestCase;
use App\Models\Company;
use App\Models\Product;
use App\Enums\UserRoles;
use App\Enums\ProductType;
use App\Enums\UnitCategory;
use App\Models\ProductUnit;
use App\Models\ProductGroup;
use App\Enums\ProductCategory;
use App\Enums\ProductGroupCategory;
use Vinkla\Hashids\Facades\Hashids;
use Database\Seeders\UnitTableSeeder;
use Database\Seeders\BrandTableSeeder;
use App\Http\Resources\ProductResource;
use Database\Seeders\ProductTableSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\ProductGroupTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Factories\Sequence;

class ProductAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /* #region store */
    public function test_product_api_call_store_product_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);

        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];
        
        $unitCount = $this->faker->numberBetween(1, 5);
        $primaryUnitIdx = $this->faker->numberBetween(0, $unitCount - 1);
        $maxConverionValue = 1;
        for ($i = 0; $i < $unitCount ; $i++) {
            $unitIdTemp = Unit::where('company_id', '=', $companyId)->where('category', '!=',  UnitCategory::SERVICES->value)->inRandomOrder()->first()->id;

            $conversionValueTemp = $i == 0 ? 1 : $this->faker->numberBetween($maxConverionValue + 1, $maxConverionValue + 20);
            $isBaseTemp = $i == 0 ? true : false;
            $isPrimaryUnitTemp = $i == $primaryUnitIdx ? true : false;

            array_push($productUnitsCode, ProductUnit::factory()->make()->code);
            array_push($unitId, Hashids::encode($unitIdTemp));
            array_push($conversionValue, $conversionValueTemp);
            array_push($isBase, $isBaseTemp);
            array_push($isPrimaryUnit, $isPrimaryUnitTemp);
            array_push($productUnitsRemarks, $this->faker->sentence());

            $maxConverionValue = $conversionValueTemp;
        }

        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId),
            'product_group_id' => Hashids::encode(ProductGroup::where('company_id', '=', $companyId)->where('category', '!=',  ProductGroupCategory::SERVICES->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_code' => $productUnitsCode,
            'unit_id' => $unitId,
            'conv_value' => $conversionValue,
            'is_base' => $isBase,
            'is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ], Product::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('products', [
            'company_id' => Hashids::decode($productArr['company_id'])[0],
            'code' => $productArr['code'],
            'product_group_id' => Hashids::decode($productArr['product_group_id']),
            'brand_id' => Hashids::decode($productArr['brand_id']),
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

        for ($i = 0; $i < count($productUnitsCode) ; $i++) {
            $this->assertDatabaseHas('product_units', [
                'company_id' => $companyId,
                'unit_id' => Hashids::decode($unitId[$i])[0],
                'code' => $productUnitsCode[$i],
                'is_base' => $isBase[$i],
                'conversion_value' => $conversionValue[$i],
                'is_primary_unit' => $isPrimaryUnit[$i],
                'remarks' => $productUnitsRemarks[$i],
            ]);
        }
    }

    public function test_product_api_call_store_service_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::SERVICES->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::SERVICES->value]);

        $productUnitsCode = [];
        array_push($productUnitsCode, ProductUnit::factory()->make()->code);

        $unitId = [];
        $unitIdTemp = Unit::where('company_id', '=', $companyId)->where('category', '!=',  ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first()->id;
        array_push($unitId, Hashids::encode($unitIdTemp));

        $conversionValue = [];
        array_push($conversionValue, 1);

        $isBase = [];
        array_push($isBase, 1);

        $isPrimaryUnit = [];
        array_push($isPrimaryUnit, 1);

        $productUnitsRemarks = [];
        array_push($productUnitsRemarks, $this->faker->sentence());

        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId),
            'product_group_id' => Hashids::encode(ProductGroup::where('company_id', '=', $companyId)->where('category', '!=',  ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'product_type' => 4,
            'product_units_code' => $productUnitsCode,
            'unit_id' => $unitId,
            'conv_value' => $conversionValue,
            'is_base' => $isBase,
            'is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ], Product::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('products', [
            'company_id' => Hashids::decode($productArr['company_id'])[0],
            'code' => $productArr['code'],
            'product_group_id' => Hashids::decode($productArr['product_group_id']),
            'brand_id' => Hashids::decode($productArr['brand_id']),
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
            'unit_id' => Hashids::decode($unitId[0])[0],
            'code' => $productUnitsCode[0],
            'is_base' => $isBase[0],
            'conversion_value' => $conversionValue[0],
            'is_primary_unit' => $isPrimaryUnit[0],
            'remarks' => $productUnitsRemarks[0],
        ]);
    }

    public function test_product_api_call_store_product_with_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

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

        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];
        
        $unitCount = $this->faker->numberBetween(1, 5);
        $primaryUnitIdx = $this->faker->numberBetween(0, $unitCount - 1);
        $maxConverionValue = 1;
        for ($i = 0; $i < $unitCount ; $i++) {
            $unitIdTemp = Unit::where('company_id', '=', $companyId)->where('category', '!=',  UnitCategory::SERVICES->value)->inRandomOrder()->first()->id;

            $conversionValueTemp = $i == 0 ? 1 : $this->faker->numberBetween($maxConverionValue + 1, $maxConverionValue + 20);
            $isBaseTemp = $i == 0 ? true : false;
            $isPrimaryUnitTemp = $i == $primaryUnitIdx ? true : false;

            array_push($productUnitsCode, ProductUnit::factory()->make()->code);
            array_push($unitId, Hashids::encode($unitIdTemp));
            array_push($conversionValue, $conversionValueTemp);
            array_push($isBase, $isBaseTemp);
            array_push($isPrimaryUnit, $isPrimaryUnitTemp);
            array_push($productUnitsRemarks, $this->faker->sentence());

            $maxConverionValue = $conversionValueTemp;
        }
        
        $productArr = Product::factory()->make()->toArray();
        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($companyId),
            'code' => $company->products()->inRandomOrder()->first()->code,
            'product_group_id' => Hashids::encode($company->productGroups()->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_code' => $productUnitsCode,
            'unit_id' => $unitId,
            'conv_value' => $conversionValue,
            'is_base' => $isBase,
            'is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_store_service_with_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::SERVICES->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::SERVICES->value]);

        $productSeeder = new ProductTableSeeder();
        $productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::SERVICES->value]);

        $productUnitsCode = [];
        array_push($productUnitsCode, ProductUnit::factory()->make()->code);

        $unitId = [];
        $unitIdTemp = Unit::where('company_id', '=', $companyId)->where('category', '!=',  ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first()->id;
        array_push($unitId, Hashids::encode($unitIdTemp));

        $conversionValue = [];
        array_push($conversionValue, 1);

        $isBase = [];
        array_push($isBase, 1);

        $isPrimaryUnit = [];
        array_push($isPrimaryUnit, 1);

        $productUnitsRemarks = [];
        array_push($productUnitsRemarks, $this->faker->sentence());

        $productArr = Product::factory()->make()->toArray();
        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($companyId),
            'code' => $company->products()->inRandomOrder()->first()->code,
            'product_group_id' => Hashids::encode(ProductGroup::where('company_id', '=', $companyId)->where('category', '!=',  ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'product_type' => 4,
            'product_units_code' => $productUnitsCode,
            'unit_id' => $unitId,
            'conv_value' => $conversionValue,
            'is_base' => $isBase,
            'is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_store_with_existing_code_in_different_company_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->count(2), 'companies')
                    ->create();

        $this->actingAs($user);

        $productGroupSeeder = new ProductGroupTableSeeder();
        $brandSeeder = new BrandTableSeeder();
        $unitSeeder = new UnitTableSeeder();
        $productSeeder = new ProductTableSeeder();

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;
        
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId_1, ProductGroupCategory::PRODUCTS->value]);
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId_1]);       
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId_1, UnitCategory::PRODUCTS->value]);       
        $productSeeder->callWith(ProductTableSeeder::class, [3, $companyId_1, ProductCategory::PRODUCTS->value]);

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;

        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId_2, ProductGroupCategory::PRODUCTS->value]);
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId_2]);       
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId_2, UnitCategory::PRODUCTS->value]);       
        $productSeeder->callWith(ProductTableSeeder::class, [3, $companyId_2, ProductCategory::PRODUCTS->value]);

        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];
        
        $unitCount = $this->faker->numberBetween(1, 5);
        $primaryUnitIdx = $this->faker->numberBetween(0, $unitCount - 1);
        $maxConverionValue = 1;
        for ($i = 0; $i < $unitCount ; $i++) {
            $unitIdTemp = Unit::where('company_id', '=', $companyId_1)->where('category', '!=',  UnitCategory::SERVICES->value)->inRandomOrder()->first()->id;

            $conversionValueTemp = $i == 0 ? 1 : $this->faker->numberBetween($maxConverionValue + 1, $maxConverionValue + 20);
            $isBaseTemp = $i == 0 ? true : false;
            $isPrimaryUnitTemp = $i == $primaryUnitIdx ? true : false;

            array_push($productUnitsCode, ProductUnit::factory()->make()->code);
            array_push($unitId, Hashids::encode($unitIdTemp));
            array_push($conversionValue, $conversionValueTemp);
            array_push($isBase, $isBaseTemp);
            array_push($isPrimaryUnit, $isPrimaryUnitTemp);
            array_push($productUnitsRemarks, $this->faker->sentence());

            $maxConverionValue = $conversionValueTemp;
        }
        
        $productArr = Product::factory()->make()->toArray();
        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($companyId_1),
            'code' => $company_2->products()->inRandomOrder()->first()->code,
            'product_group_id' => Hashids::encode($company_1->productGroups()->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company_1->brands()->inRandomOrder()->first()->id),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_code' => $productUnitsCode,
            'unit_id' => $unitId,
            'conv_value' => $conversionValue,
            'is_base' => $isBase,
            'is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('products', [
            'company_id' => $companyId_1,
            'code' => $productArr['code'],
            'product_group_id' => Hashids::decode($productArr['product_group_id']),
            'brand_id' => Hashids::decode($productArr['brand_id']),
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

        for ($i = 0; $i < count($productUnitsCode) ; $i++) {
            $this->assertDatabaseHas('product_units', [
                'company_id' => $companyId_1,
                'unit_id' => Hashids::decode($unitId[$i])[0],
                'code' => $productUnitsCode[$i],
                'is_base' => $isBase[$i],
                'conversion_value' => $conversionValue[$i],
                'is_primary_unit' => $isPrimaryUnit[$i],
                'remarks' => $productUnitsRemarks[$i],
            ]);
        }
    }

    public function test_product_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                ->has(Company::factory()->setIsDefault(), 'companies')
                ->create();

        $this->actingAs($user);

        $productArr = [];
        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertJsonValidationErrors(['company_id', 'code', 'name']);
    }
    /* #endregion */

    /* #region list */
    public function test_product_api_call_list_with_or_without_pagination_expect_paginator_or_collection()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [5, $companyId, ProductGroupCategory::PRODUCTS_AND_SERVICES->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [5, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [5, $companyId, UnitCategory::PRODUCTS_AND_SERVICES->value]);

        $productSeeder = new ProductTableSeeder();
        $productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS_AND_SERVICES->value]);

        $api = $this->getJson(route('api.get.db.product.product.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => '',
            'paginate' => true,
            'page' => 1,
            'perPage' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);

        $api = $this->getJson(route('api.get.db.product.service.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => '',
            'paginate' => true,
            'page' => 1,
            'perPage' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }

    public function test_product_api_call_list_product_with_search_expect_filtered_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [5, $companyId, ProductGroupCategory::PRODUCTS->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [5, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [5, $companyId, UnitCategory::PRODUCTS->value]);

        $productSeeder = new ProductTableSeeder();
        $productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::PRODUCTS->value]);

        $exampleCount = 3;
        $someProducts = $company->products()->inRandomOrder()->take($exampleCount)->get();
        for ($i = 0; $i < $exampleCount; $i++) {
            $product = $someProducts[$i];
            $product->name = substr_replace($product->name, 'testing', random_int(0, strlen($product->name) - 1), 0);
            $product->save();
        }

        $api = $this->getJson(route('api.get.db.product.product.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => 'testing',
            'paginate' => true,
            'page' => 1,
            'perPage' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);

        $api->assertJsonFragment([
            'total' => $exampleCount,
        ]);
    }

    public function test_product_api_call_list_service_with_search_expect_filtered_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [5, $companyId, ProductGroupCategory::SERVICES->value]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [5, $companyId, UnitCategory::SERVICES->value]);

        $productSeeder = new ProductTableSeeder();
        $productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::SERVICES->value]);

        $exampleCount = 3;
        $someProducts = $company->products()->inRandomOrder()->take($exampleCount)->get();
        for ($i = 0; $i < $exampleCount; $i++) {
            $product = $someProducts[$i];
            $product->name = substr_replace($product->name, 'testing', random_int(0, strlen($product->name) - 1), 0);
            $product->save();
        }

        $api = $this->getJson(route('api.get.db.product.service.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => 'testing',
            'paginate' => true,
            'page' => 1,
            'perPage' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);

        $api->assertJsonFragment([
            'total' => $exampleCount,
        ]);
    }

    public function test_product_api_call_list_without_search_querystring_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $companyId = $user->companies->first()->id;

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [5, $companyId, ProductGroupCategory::PRODUCTS_AND_SERVICES->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [5, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [5, $companyId, UnitCategory::PRODUCTS_AND_SERVICES->value]);

        $productSeeder = new ProductTableSeeder();
        $productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::PRODUCTS_AND_SERVICES->value]);

        $api = $this->getJson(route('api.get.db.product.product.list', [
            'companyId' => Hashids::encode($companyId),
        ]));

        $api->assertStatus(422);
    }

    public function test_product_api_call_list_with_special_char_in_search_expect_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);
        
        $companyId = $user->companies->first()->id;

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [5, $companyId, ProductGroupCategory::PRODUCTS_AND_SERVICES->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [5, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [5, $companyId, UnitCategory::PRODUCTS_AND_SERVICES->value]);

        $productSeeder = new ProductTableSeeder();
        $productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::PRODUCTS_AND_SERVICES->value]);

        $api = $this->getJson(route('api.get.db.product.product.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~",
            'paginate' => true,
            'page' => 1,
            'perPage' => 10,
            'refresh' => false,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }

    public function test_product_api_call_list_with_negative_value_in_parameters_expect_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $companyId = $user->companies->first()->id;      

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [5, $companyId, ProductGroupCategory::PRODUCTS_AND_SERVICES->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [5, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [5, $companyId, UnitCategory::PRODUCTS_AND_SERVICES->value]);

        $productSeeder = new ProductTableSeeder();
        $productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::PRODUCTS_AND_SERVICES->value]);

        $api = $this->getJson(route('api.get.db.product.product.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => '',
            'paginate' => true,
            'page' => -1,
            'perPage' => -10,
            'refresh' => false,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }
    /* #endregion */

    /* #region read */
    public function test_product_api_call_read_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS_AND_SERVICES->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS_AND_SERVICES->value]);

        $productSeeder = new ProductTableSeeder();
        $productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS_AND_SERVICES->value]);

        $productUuid = $company->products()->where('product_type', '!=', ProductType::SERVICE->value)->inRandomOrder()->first()->uuid;

        $api = $this->getJson(route('api.get.db.product.product.read', $productUuid));

        $api->assertSuccessful();

        $serviceUuid = $company->products()->where('product_type', '=', ProductType::SERVICE->value)->inRandomOrder()->first()->uuid;

        $api = $this->getJson(route('api.get.db.product.service.read', $serviceUuid));

        $api->assertSuccessful();
    }

    public function test_product_api_call_read_without_uuid_expect_exception()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();

        $this->getJson(route('api.get.db.product.product.read', null));
    }

    public function test_product_api_call_read_with_nonexistance_uuid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $uuid = $this->faker->uuid();

        $api = $this->getJson(route('api.get.db.product.product.read', $uuid));

        $api->assertStatus(404);
    }
    /* #endregion */

    /* #region update */
    public function test_product_api_call_update_product_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

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

        $product_units_hId = [];
        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];
        
        $unitCount = $this->faker->numberBetween(1, 5);
        $primaryUnitIdx = $this->faker->numberBetween(0, $unitCount - 1);
        $maxConverionValue = 1;
        for ($i = 0; $i < $unitCount ; $i++) {
            $unitIdTemp = Unit::where('company_id', '=', $companyId)->where('category', '!=', UnitCategory::SERVICES->value)->inRandomOrder()->first()->id;

            $conversionValueTemp = $i == 0 ? 1 : $this->faker->numberBetween($maxConverionValue + 1, $maxConverionValue + 20);
            $isBaseTemp = $i == 0 ? true : false;
            $isPrimaryUnitTemp = $i == $primaryUnitIdx ? true : false;

            array_push($product_units_hId, 0);
            array_push($productUnitsCode, ProductUnit::factory()->make()->code);
            array_push($unitId, Hashids::encode($unitIdTemp));
            array_push($conversionValue, $conversionValueTemp);
            array_push($isBase, $isBaseTemp);
            array_push($isPrimaryUnit, $isPrimaryUnitTemp);
            array_push($productUnitsRemarks, $this->faker->sentence());

            $maxConverionValue = $conversionValueTemp;
        }

        $product = $company->products()->where('product_type', '!=', 4)->inRandomOrder()->first();
        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId),
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '!=', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_hId' => $product_units_hId,
            'product_units_code' => $productUnitsCode,
            'unit_id' => $unitId,
            'conv_value' => $conversionValue,
            'is_base' => $isBase,
            'is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ], Product::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->uuid), $productArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('products', [
            'company_id' => Hashids::decode($productArr['company_id'])[0],
            'code' => $productArr['code'],
            'product_group_id' => Hashids::decode($productArr['product_group_id']),
            'brand_id' => Hashids::decode($productArr['brand_id']),
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

        for ($i = 0; $i < count($productUnitsCode) ; $i++) {
            $this->assertDatabaseHas('product_units', [
                'company_id' => $companyId,
                'unit_id' => Hashids::decode($unitId[$i])[0],
                'code' => $productUnitsCode[$i],
                'is_base' => $isBase[$i],
                'conversion_value' => $conversionValue[$i],
                'is_primary_unit' => $isPrimaryUnit[$i],
                'remarks' => $productUnitsRemarks[$i],
            ]);
        }
    }

    public function test_product_api_call_update_service_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::SERVICES->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::SERVICES->value]);

        $productSeeder = new ProductTableSeeder();
        $productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::SERVICES->value]);

        $product_units_hId = [];
        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];
        $unitIdTemp = Unit::where('company_id', '=', $companyId)->where('category', '!=', UnitCategory::PRODUCTS->value)->inRandomOrder()->first()->id;

        array_push($product_units_hId, 0);
        array_push($productUnitsCode, ProductUnit::factory()->make()->code);
        array_push($unitId, Hashids::encode($unitIdTemp));
        array_push($conversionValue, 1);
        array_push($isBase, 1);
        array_push($isPrimaryUnit, 1);
        array_push($productUnitsRemarks, $this->faker->sentence());


        $product = $company->products()->where('product_type', '=', 4)->inRandomOrder()->first();
        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId),
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '!=', ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_hId' => $product_units_hId,
            'product_units_code' => $productUnitsCode,
            'unit_id' => $unitId,
            'conv_value' => $conversionValue,
            'is_base' => $isBase,
            'is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ], Product::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->uuid), $productArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('products', [
            'company_id' => Hashids::decode($productArr['company_id'])[0],
            'code' => $productArr['code'],
            'product_group_id' => Hashids::decode($productArr['product_group_id']),
            'brand_id' => Hashids::decode($productArr['brand_id']),
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
            'unit_id' => Hashids::decode($unitId[0])[0],
            'code' => $productUnitsCode[0],
            'is_base' => $isBase[0],
            'conversion_value' => $conversionValue[0],
            'is_primary_unit' => $isPrimaryUnit[0],
            'remarks' => $productUnitsRemarks[0],
        ]);
    }

    public function test_product_api_call_update_product_and_use_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

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

        $products = $company->products()->where('product_type', '!=', 4)->inRandomOrder()->take(2)->get();
        $product_1 = $products[0];
        $product_2 = $products[1];

        $product_units_hId = [];
        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];
        
        $unitCount = $this->faker->numberBetween(1, 5);
        $primaryUnitIdx = $this->faker->numberBetween(0, $unitCount - 1);
        $maxConverionValue = 1;
        for ($i = 0; $i < $unitCount ; $i++) {
            $unitIdTemp = Unit::where('company_id', '=', $companyId)->where('category', '!=', UnitCategory::SERVICES->value)->inRandomOrder()->first()->id;

            $conversionValueTemp = $i == 0 ? 1 : $this->faker->numberBetween($maxConverionValue + 1, $maxConverionValue + 20);
            $isBaseTemp = $i == 0 ? true : false;
            $isPrimaryUnitTemp = $i == $primaryUnitIdx ? true : false;

            array_push($product_units_hId, 0);
            array_push($productUnitsCode, ProductUnit::factory()->make()->code);
            array_push($unitId, Hashids::encode($unitIdTemp));
            array_push($conversionValue, $conversionValueTemp);
            array_push($isBase, $isBaseTemp);
            array_push($isPrimaryUnit, $isPrimaryUnitTemp);
            array_push($productUnitsRemarks, $this->faker->sentence());

            $maxConverionValue = $conversionValueTemp;
        }

        $productArr = Product::factory()->make()->toArray();
        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($companyId),
            'code' => $product_1->code,
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '!=', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_hId' => $product_units_hId,
            'product_units_code' => $productUnitsCode,
            'unit_id' => $unitId,
            'conv_value' => $conversionValue,
            'is_base' => $isBase,
            'is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product_2->uuid), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_update_service_and_use_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::SERVICES->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::SERVICES->value]);

        $productSeeder = new ProductTableSeeder();
        $productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::SERVICES->value]);

        $product_units_hId = [];
        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];
        $unitIdTemp = Unit::where('company_id', '=', $companyId)->where('category', '!=', UnitCategory::PRODUCTS->value)->inRandomOrder()->first()->id;

        array_push($product_units_hId, 0);
        array_push($productUnitsCode, ProductUnit::factory()->make()->code);
        array_push($unitId, Hashids::encode($unitIdTemp));
        array_push($conversionValue, 1);
        array_push($isBase, 1);
        array_push($isPrimaryUnit, 1);
        array_push($productUnitsRemarks, $this->faker->sentence());

        $products = $company->products()->where('product_type', '=', 4)->inRandomOrder()->take(2)->get();
        $product_1 = $products[0];
        $product_2 = $products[1];

        $productArr = Product::factory()->make()->toArray();
        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($companyId),
            'code' => $product_1->code,
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '!=', ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_hId' => $product_units_hId,
            'product_units_code' => $productUnitsCode,
            'unit_id' => $unitId,
            'conv_value' => $conversionValue,
            'is_base' => $isBase,
            'is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product_2->uuid), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_update_product_and_use_existing_code_in_different_company_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->count(2)->state(new Sequence(['default' => true], ['default' => false])), 'companies')
                    ->create();
        
        $this->actingAs($user);
        
        $productGroupSeeder = new ProductGroupTableSeeder();
        $brandSeeder = new BrandTableSeeder();
        $unitSeeder = new UnitTableSeeder();
        $productSeeder = new ProductTableSeeder();    

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;

        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId_1, ProductGroupCategory::PRODUCTS->value]);
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId_1]);
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId_1, UnitCategory::PRODUCTS->value]);
        $productSeeder->callWith(ProductTableSeeder::class, [3, $companyId_1, ProductCategory::PRODUCTS->value]);

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;

        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId_2, ProductGroupCategory::PRODUCTS->value]);
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId_2]);
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId_2, UnitCategory::PRODUCTS->value]);
        $productSeeder->callWith(ProductTableSeeder::class, [3, $companyId_2, ProductCategory::PRODUCTS->value]);

        $product_units_hId = [];
        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];
        
        $unitCount = $this->faker->numberBetween(1, 5);
        $primaryUnitIdx = $this->faker->numberBetween(0, $unitCount - 1);
        $maxConverionValue = 1;
        for ($i = 0; $i < $unitCount ; $i++) {
            $unitIdTemp = Unit::where('company_id', '=', $companyId_2)->where('category', '!=', UnitCategory::SERVICES->value)->inRandomOrder()->first()->id;

            $conversionValueTemp = $i == 0 ? 1 : $this->faker->numberBetween($maxConverionValue + 1, $maxConverionValue + 20);
            $isBaseTemp = $i == 0 ? true : false;
            $isPrimaryUnitTemp = $i == $primaryUnitIdx ? true : false;

            array_push($product_units_hId, 0);
            array_push($productUnitsCode, ProductUnit::factory()->make()->code);
            array_push($unitId, Hashids::encode($unitIdTemp));
            array_push($conversionValue, $conversionValueTemp);
            array_push($isBase, $isBaseTemp);
            array_push($isPrimaryUnit, $isPrimaryUnitTemp);
            array_push($productUnitsRemarks, $this->faker->sentence());

            $maxConverionValue = $conversionValueTemp;
        }

        $productArr = Product::factory()->make()->toArray();
        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($companyId_2),
            'code' => $company_1->products()->first()->id,
            'product_group_id' => Hashids::encode($company_2->productGroups()->where('category', '!=', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company_2->brands()->inRandomOrder()->first()->id),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_hId' => $product_units_hId,
            'product_units_code' => $productUnitsCode,
            'unit_id' => $unitId,
            'conv_value' => $conversionValue,
            'is_base' => $isBase,
            'is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.edit', $company_2->products()->first()->uuid), $productArr);

        $api->assertSuccessful();
    }

    public function test_product_api_call_update_service_and_use_existing_code_in_different_company_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->count(2)->state(new Sequence(['default' => true], ['default' => false])), 'companies')
                    ->create();
        
        $this->actingAs($user);
        
        $productGroupSeeder = new ProductGroupTableSeeder();
        $brandSeeder = new BrandTableSeeder();
        $unitSeeder = new UnitTableSeeder();
        $productSeeder = new ProductTableSeeder();

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;

        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId_1, ProductGroupCategory::SERVICES->value]);
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId_1]);
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId_1, UnitCategory::SERVICES->value]);
        $productSeeder->callWith(ProductTableSeeder::class, [3, $companyId_1, ProductCategory::SERVICES->value]);

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;

        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId_2, ProductGroupCategory::SERVICES->value]);
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId_2]);
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId_2, UnitCategory::SERVICES->value]);
        $productSeeder->callWith(ProductTableSeeder::class, [3, $companyId_2, ProductCategory::SERVICES->value]);

        $product_units_hId = [];
        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];
        $unitIdTemp = Unit::where('company_id', '=', $companyId_2)->where('category', '!=', UnitCategory::PRODUCTS->value)->inRandomOrder()->first()->id;

        array_push($product_units_hId, 0);
        array_push($productUnitsCode, ProductUnit::factory()->make()->code);
        array_push($unitId, Hashids::encode($unitIdTemp));
        array_push($conversionValue, 1);
        array_push($isBase, 1);
        array_push($isPrimaryUnit, 1);
        array_push($productUnitsRemarks, $this->faker->sentence());

        $productArr = Product::factory()->make()->toArray();
        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($companyId_2),
            'code' => $company_1->products()->where('product_type', '=', 4)->inRandomOrder()->first()->code,
            'product_group_id' => Hashids::encode($company_2->productGroups()->where('category', '!=', ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company_2->brands()->inRandomOrder()->first()->id),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_hId' => $product_units_hId,
            'product_units_code' => $productUnitsCode,
            'unit_id' => $unitId,
            'conv_value' => $conversionValue,
            'is_base' => $isBase,
            'is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.edit', $company_2->products()->first()->uuid), $productArr);

        $api->assertSuccessful();
    }
    /* #endregion */

    /* #region delete */
    public function test_product_api_call_delete_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS_AND_SERVICES->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS_AND_SERVICES->value]);

        $productSeeder = new ProductTableSeeder();
        $productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS_AND_SERVICES->value]);

        $product = $company->products()->inRandomOrder()->first();

        $api = $this->json('POST', route('api.post.db.product.product.delete', $product->uuid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    public function test_product_api_call_delete_of_nonexistance_uuid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $uuid = $this->faker->uuid();
   
        $api = $this->json('POST', route('api.post.db.product.product.delete', $uuid));

        $api->assertStatus(404);
    }

    public function test_product_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);

        $api = $this->json('POST', route('api.post.db.product.product.delete', null));
    }
    /* #endregion */

    /* #region others */
    public function test_product_api_call_get_product_type_expect_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.product.common.list.product_type', [
            'type' => 'products'
        ]));

        $api->assertSuccessful();

        $api->assertJsonStructure([
            ['name']
        ]);

        $api = $this->getJson(route('api.get.db.product.common.list.product_type', [
            'type' => 'service'
        ]));

        $api->assertSuccessful();

        $api->assertJsonStructure([
            ['name']
        ]);

        $api = $this->getJson(route('api.get.db.product.common.list.product_type'));

        $api->assertSuccessful();

        $api->assertJsonStructure([
            ['name']
        ]);
    }
    /* #endregion */
}
