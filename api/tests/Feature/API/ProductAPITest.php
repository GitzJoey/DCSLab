<?php

namespace Tests\Feature\API;

use Exception;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Models\Brand;
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
use Database\Seeders\ProductTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\ProductGroupTableSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

class ProductAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->productGroupSeeder = new ProductGroupTableSeeder();
        $this->brandSeeder = new BrandTableSeeder();
        $this->unitSeeder = new UnitTableSeeder();
        $this->productSeeder = new ProductTableSeeder();
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

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);

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
            $unitIdTemp = $company->Units()->where('category', '!=',  UnitCategory::SERVICES->value)->inRandomOrder()->first()->id;

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
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
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

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::SERVICES->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::SERVICES->value]);

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
            'product_type' => ProductType::SERVICE->value,
            'product_units_code' => $productUnitsCode,
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
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

    public function test_product_api_call_store_product_with_nonexistance_product_group_id_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);

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
            $unitIdTemp = $company->Units()->where('category', '!=',  UnitCategory::SERVICES->value)->inRandomOrder()->first()->id;

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
            'product_group_id' => Hashids::encode(ProductGroup::max('id') + 1),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_code' => $productUnitsCode,
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ], Product::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_store_product_with_nonexistance_brand_id_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);

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
            $unitIdTemp = $company->Units()->where('category', '!=',  UnitCategory::SERVICES->value)->inRandomOrder()->first()->id;

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
            'brand_id' => Hashids::encode(Brand::max('id') + 1),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_code' => $productUnitsCode,
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ], Product::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_store_product_with_nonexistance_product_unit_unit_id_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);

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
            $unitIdTemp = $company->Units()->where('category', '!=',  UnitCategory::SERVICES->value)->inRandomOrder()->first()->id;
            if ($i == 0) {
                $unitIdTemp = Unit::max('id') + 1;
            }

            if ($i == $unitCount -1) {
                $unitIdTemp = Unit::max('id') + 2;
            }

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
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ], Product::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
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

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS->value]);

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
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
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

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::SERVICES->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::SERVICES->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::SERVICES->value]);

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
            'product_type' => ProductType::SERVICE->value,
            'product_units_code' => $productUnitsCode,
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
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

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;
        
        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId_1, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId_1]);       
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId_1, UnitCategory::PRODUCTS->value]);       
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId_1, ProductCategory::PRODUCTS->value]);

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId_2, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId_2]);       
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId_2, UnitCategory::PRODUCTS->value]);       
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId_2, ProductCategory::PRODUCTS->value]);

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
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
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

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [5, $companyId, ProductGroupCategory::PRODUCTS_AND_SERVICES->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [5, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [5, $companyId, UnitCategory::PRODUCTS_AND_SERVICES->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS_AND_SERVICES->value]);

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

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [5, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [5, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [5, $companyId, UnitCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::PRODUCTS->value]);

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

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [5, $companyId, ProductGroupCategory::SERVICES->value]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [5, $companyId, UnitCategory::SERVICES->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::SERVICES->value]);

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

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [5, $companyId, ProductGroupCategory::PRODUCTS_AND_SERVICES->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [5, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [5, $companyId, UnitCategory::PRODUCTS_AND_SERVICES->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::PRODUCTS_AND_SERVICES->value]);

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

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [5, $companyId, ProductGroupCategory::PRODUCTS_AND_SERVICES->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [5, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [5, $companyId, UnitCategory::PRODUCTS_AND_SERVICES->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::PRODUCTS_AND_SERVICES->value]);

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

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [5, $companyId, ProductGroupCategory::PRODUCTS_AND_SERVICES->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [5, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [5, $companyId, UnitCategory::PRODUCTS_AND_SERVICES->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::PRODUCTS_AND_SERVICES->value]);

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

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS_AND_SERVICES->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS_AND_SERVICES->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS_AND_SERVICES->value]);

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
    public function test_product_api_call_update_product_and_insert_product_units_expect_db_updated()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS->value]);

        $product = $company->products()->where('product_type', '!=', ProductType::SERVICE->value)->inRandomOrder()->first();

        $product_units_hId = [];
        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];

        $productUnitsArr = $product->productUnits->toArray();
        for ($i = 0; $i < count($productUnitsArr) ; $i++) {
            array_push($product_units_hId, Hashids::encode($productUnitsArr[$i]['id']));
            array_push($productUnitsCode, $productUnitsArr[$i]['code']);
            array_push($unitId, Hashids::encode($productUnitsArr[$i]['unit_id']));
            array_push($conversionValue, $productUnitsArr[$i]['conversion_value']);
            array_push($isBase, $productUnitsArr[$i]['is_base']);
            array_push($isPrimaryUnit, $productUnitsArr[$i]['is_primary_unit']);
            array_push($productUnitsRemarks, $productUnitsArr[$i]['remarks']);
        }

        array_push($product_units_hId, null);
        array_push($productUnitsCode, $this->faker->numberBetween(1000000, 9999999));
        array_push($unitId, Hashids::encode($company->units()->where('category', '<>', ProductCategory::SERVICES->value)->inRandomOrder()->first()->id));
        array_push($conversionValue, $productUnitsArr[count($productUnitsArr) - 1]['conversion_value'] * 2);
        array_push($isBase, false);
        array_push($isPrimaryUnit, false);
        array_push($productUnitsRemarks, $this->faker->sentence());

        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId),
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '!=', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_hId' => $product_units_hId,
            'product_units_code' => $productUnitsCode,
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
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

    public function test_product_api_call_update_product_with_nonexistance_product_group_id_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS->value]);

        $product = $company->products()->where('product_type', '!=', ProductType::SERVICE->value)->inRandomOrder()->first();

        $product_units_hId = [];
        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];

        $productUnitsArr = $product->productUnits->toArray();
        for ($i = 0; $i < count($productUnitsArr) ; $i++) {
            array_push($product_units_hId, Hashids::encode($productUnitsArr[$i]['id']));
            array_push($productUnitsCode, $productUnitsArr[$i]['code']);
            array_push($unitId, Hashids::encode($productUnitsArr[$i]['unit_id']));
            array_push($conversionValue, $productUnitsArr[$i]['conversion_value']);
            array_push($isBase, $productUnitsArr[$i]['is_base']);
            array_push($isPrimaryUnit, $productUnitsArr[$i]['is_primary_unit']);
            array_push($productUnitsRemarks, $productUnitsArr[$i]['remarks']);
        }

        array_push($product_units_hId, null);
        array_push($productUnitsCode, $this->faker->numberBetween(1000000, 9999999));
        array_push($unitId, Hashids::encode($company->units()->where('category', '<>', ProductCategory::SERVICES->value)->inRandomOrder()->first()->id));
        array_push($conversionValue, $productUnitsArr[count($productUnitsArr) - 1]['conversion_value'] * 2);
        array_push($isBase, false);
        array_push($isPrimaryUnit, false);
        array_push($productUnitsRemarks, $this->faker->sentence());

        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId),
            'product_group_id' => Hashids::encode(ProductGroup::max('id') + 1),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_hId' => $product_units_hId,
            'product_units_code' => $productUnitsCode,
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ], Product::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->uuid), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_update_product_with_nonexistance_brand_id_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS->value]);

        $product = $company->products()->where('product_type', '!=', ProductType::SERVICE->value)->inRandomOrder()->first();

        $product_units_hId = [];
        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];

        $productUnitsArr = $product->productUnits->toArray();
        for ($i = 0; $i < count($productUnitsArr) ; $i++) {
            array_push($product_units_hId, Hashids::encode($productUnitsArr[$i]['id']));
            array_push($productUnitsCode, $productUnitsArr[$i]['code']);
            array_push($unitId, Hashids::encode($productUnitsArr[$i]['unit_id']));
            array_push($conversionValue, $productUnitsArr[$i]['conversion_value']);
            array_push($isBase, $productUnitsArr[$i]['is_base']);
            array_push($isPrimaryUnit, $productUnitsArr[$i]['is_primary_unit']);
            array_push($productUnitsRemarks, $productUnitsArr[$i]['remarks']);
        }

        array_push($product_units_hId, null);
        array_push($productUnitsCode, $this->faker->numberBetween(1000000, 9999999));
        array_push($unitId, Hashids::encode($company->units()->where('category', '<>', ProductCategory::SERVICES->value)->inRandomOrder()->first()->id));
        array_push($conversionValue, $productUnitsArr[count($productUnitsArr) - 1]['conversion_value'] * 2);
        array_push($isBase, false);
        array_push($isPrimaryUnit, false);
        array_push($productUnitsRemarks, $this->faker->sentence());

        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId),
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '!=', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode(ProductGroup::max('id') + 1),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_hId' => $product_units_hId,
            'product_units_code' => $productUnitsCode,
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ], Product::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->uuid), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_update_product_with_nonexistance_product_unit_unit_id_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS->value]);

        $product = $company->products()->where('product_type', '!=', ProductType::SERVICE->value)->inRandomOrder()->first();

        $product_units_hId = [];
        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];

        $productUnitsArr = $product->productUnits->toArray();
        for ($i = 0; $i < count($productUnitsArr) ; $i++) {
            $unit_hId = $productUnitsArr[$i]['unit_id'];
            if ($i == 0) {
                $unit_hId = Unit::max('id') + 1;
            }

            if ($i == count($productUnitsArr) - 1) {
                $unit_hId = Unit::max('id') + 2;
            }

            array_push($product_units_hId, Hashids::encode($productUnitsArr[$i]['id']));
            array_push($productUnitsCode, $productUnitsArr[$i]['code']);
            array_push($unitId, Hashids::encode($unit_hId));
            array_push($conversionValue, $productUnitsArr[$i]['conversion_value']);
            array_push($isBase, $productUnitsArr[$i]['is_base']);
            array_push($isPrimaryUnit, $productUnitsArr[$i]['is_primary_unit']);
            array_push($productUnitsRemarks, $productUnitsArr[$i]['remarks']);
        }

        array_push($product_units_hId, null);
        array_push($productUnitsCode, $this->faker->numberBetween(1000000, 9999999));
        array_push($unitId, Hashids::encode($company->units()->where('category', '<>', ProductCategory::SERVICES->value)->inRandomOrder()->first()->id));
        array_push($conversionValue, $productUnitsArr[count($productUnitsArr) - 1]['conversion_value'] * 2);
        array_push($isBase, false);
        array_push($isPrimaryUnit, false);
        array_push($productUnitsRemarks, $this->faker->sentence());

        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId),
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '!=', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_hId' => $product_units_hId,
            'product_units_code' => $productUnitsCode,
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ], Product::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->uuid), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_update_product_and_insert_product_units_with_nonexistance_product_unit_id_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS->value]);

        $product = $company->products()->where('product_type', '!=', ProductType::SERVICE->value)->inRandomOrder()->first();

        $product_units_hId = [];
        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];

        $productUnitsArr = $product->productUnits->toArray();
        for ($i = 0; $i < count($productUnitsArr) ; $i++) {
            $productUnitId = Hashids::encode($productUnitsArr[$i]['id']);
            if ($i == 0) {
                $productUnitId = Hashids::encode(ProductUnit::max('id') + 1);
            }

            array_push($product_units_hId, $productUnitId);
            array_push($productUnitsCode, $productUnitsArr[$i]['code']);
            array_push($unitId, Hashids::encode($productUnitsArr[$i]['unit_id']));
            array_push($conversionValue, $productUnitsArr[$i]['conversion_value']);
            array_push($isBase, $productUnitsArr[$i]['is_base']);
            array_push($isPrimaryUnit, $productUnitsArr[$i]['is_primary_unit']);
            array_push($productUnitsRemarks, $productUnitsArr[$i]['remarks']);
        }

        array_push($product_units_hId, null);
        array_push($productUnitsCode, $this->faker->numberBetween(1000000, 9999999));
        array_push($unitId, Hashids::encode($company->units()->where('category', '<>', ProductCategory::SERVICES->value)->inRandomOrder()->first()->id));
        array_push($conversionValue, $productUnitsArr[count($productUnitsArr) - 1]['conversion_value'] * 2);
        array_push($isBase, false);
        array_push($isPrimaryUnit, false);
        array_push($productUnitsRemarks, $this->faker->sentence());

        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId),
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '!=', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_hId' => $product_units_hId,
            'product_units_code' => $productUnitsCode,
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ], Product::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->uuid), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_update_product_and_insert_product_units_with_non_numeric_conv_code_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS->value]);

        $product = $company->products()->where('product_type', '!=', ProductType::SERVICE->value)->inRandomOrder()->first();

        $product_units_hId = [];
        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];

        $productUnitsArr = $product->productUnits->toArray();
        for ($i = 0; $i < count($productUnitsArr) ; $i++) {
            array_push($product_units_hId, Hashids::encode($productUnitsArr[$i]['id']));
            array_push($productUnitsCode, $productUnitsArr[$i]['code']);
            array_push($unitId, Hashids::encode($productUnitsArr[$i]['unit_id']));
            array_push($conversionValue, $productUnitsArr[$i]['conversion_value']);
            array_push($isBase, $productUnitsArr[$i]['is_base']);
            array_push($isPrimaryUnit, $productUnitsArr[$i]['is_primary_unit']);
            array_push($productUnitsRemarks, $productUnitsArr[$i]['remarks']);
        }

        array_push($product_units_hId, null);
        array_push($productUnitsCode, $this->faker->numberBetween(1000000, 9999999));
        array_push($unitId, Hashids::encode($company->units()->where('category', '<>', ProductCategory::SERVICES->value)->inRandomOrder()->first()->id));
        array_push($conversionValue, 'test');
        array_push($isBase, false);
        array_push($isPrimaryUnit, false);
        array_push($productUnitsRemarks, $this->faker->sentence());

        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId),
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '!=', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_hId' => $product_units_hId,
            'product_units_code' => $productUnitsCode,
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
            'product_units_remarks' => $productUnitsRemarks,
        ], Product::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->uuid), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_update_product_and_edit_product_units_expect_db_updated()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS->value]);

        $product = $company->products()->where('product_type', '!=', ProductType::SERVICE->value)->inRandomOrder()->first();

        $product_units_hId = [];
        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];

        $productUnitsArr = $product->productUnits->toArray();
        for ($i = 0; $i < count($productUnitsArr) ; $i++) {
            array_push($product_units_hId, Hashids::encode($productUnitsArr[$i]['id']));
            array_push($productUnitsCode, $productUnitsArr[$i]['code']);
            array_push($unitId, Hashids::encode($productUnitsArr[$i]['unit_id']));
            array_push($conversionValue, $productUnitsArr[$i]['conversion_value']);
            array_push($isBase, $productUnitsArr[$i]['is_base']);
            array_push($isPrimaryUnit, $productUnitsArr[$i]['is_primary_unit']);
            array_push($productUnitsRemarks, $productUnitsArr[$i]['remarks']);
        }

        $lastRow = count($product_units_hId) - 1;
        $product_units_hId[$lastRow] = null;
        $productUnitsCode[$lastRow] = $this->faker->numberBetween(1000000, 9999999);
        $unitId[$lastRow] = Hashids::encode($company->units()->where('category', '<>', ProductCategory::SERVICES->value)->inRandomOrder()->first()->id);
        $conversionValue[$lastRow] = $productUnitsArr[count($productUnitsArr) - 1]['conversion_value'] * 2;
        $isBase[$lastRow] = false;
        $isPrimaryUnit[$lastRow] = false;
        $productUnitsRemarks[$lastRow] = $this->faker->sentence();

        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId),
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '!=', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_hId' => $product_units_hId,
            'product_units_code' => $productUnitsCode,
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
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

    public function test_product_api_call_update_product_and_delete_product_units_expect_db_updated()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS->value]);

        do {
            $product = $company->products()->where('product_type', '!=', ProductType::SERVICE->value)->inRandomOrder()->first();
        } while ($product->ProductUnits()->count() == 1);

        $product_units_hId = [];
        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];

        $productUnitsArr = $product->productUnits->toArray();
        for ($i = 0; $i < count($productUnitsArr) ; $i++) {
            array_push($product_units_hId, Hashids::encode($productUnitsArr[$i]['id']));
            array_push($productUnitsCode, $productUnitsArr[$i]['code']);
            array_push($unitId, Hashids::encode($productUnitsArr[$i]['unit_id']));
            array_push($conversionValue, $productUnitsArr[$i]['conversion_value']);
            array_push($isBase, $productUnitsArr[$i]['is_base']);
            array_push($isPrimaryUnit, $productUnitsArr[$i]['is_primary_unit']);
            array_push($productUnitsRemarks, $productUnitsArr[$i]['remarks']);
        }

        array_pop($product_units_hId);
        array_pop($productUnitsCode);
        array_pop($unitId);
        array_pop($conversionValue);
        array_pop($isBase);
        array_pop($isPrimaryUnit);
        array_pop($productUnitsRemarks);

        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId),
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '!=', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_hId' => $product_units_hId,
            'product_units_code' => $productUnitsCode,
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
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

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS->value]);

        $products = $company->products()->where('product_type', '!=', ProductType::SERVICE->value)->inRandomOrder()->take(2)->get();
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

            array_push($product_units_hId, '');
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
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
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

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::SERVICES->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::SERVICES->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::SERVICES->value]);

        $product_units_hId = [];
        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];
        $unitIdTemp = Unit::where('company_id', '=', $companyId)->where('category', '!=', UnitCategory::PRODUCTS->value)->inRandomOrder()->first()->id;

        array_push($product_units_hId, '');
        array_push($productUnitsCode, ProductUnit::factory()->make()->code);
        array_push($unitId, Hashids::encode($unitIdTemp));
        array_push($conversionValue, 1);
        array_push($isBase, 1);
        array_push($isPrimaryUnit, 1);
        array_push($productUnitsRemarks, $this->faker->sentence());

        $products = $company->products()->where('product_type', '=', ProductType::SERVICE->value)->inRandomOrder()->take(2)->get();
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
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
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
        

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId_1, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId_1]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId_1, UnitCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId_1, ProductCategory::PRODUCTS->value]);

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId_2, ProductGroupCategory::PRODUCTS->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId_2]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId_2, UnitCategory::PRODUCTS->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId_2, ProductCategory::PRODUCTS->value]);

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

            array_push($product_units_hId, '');
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
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
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
    

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId_1, ProductGroupCategory::SERVICES->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId_1]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId_1, UnitCategory::SERVICES->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId_1, ProductCategory::SERVICES->value]);

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId_2, ProductGroupCategory::SERVICES->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId_2]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId_2, UnitCategory::SERVICES->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId_2, ProductCategory::SERVICES->value]);

        $product_units_hId = [];
        $productUnitsCode = [];
        $unitId = [];
        $conversionValue = [];
        $isBase = [];
        $isPrimaryUnit = [];
        $productUnitsRemarks = [];
        $unitIdTemp = Unit::where('company_id', '=', $companyId_2)->where('category', '!=', UnitCategory::PRODUCTS->value)->inRandomOrder()->first()->id;

        array_push($product_units_hId, '');
        array_push($productUnitsCode, ProductUnit::factory()->make()->code);
        array_push($unitId, Hashids::encode($unitIdTemp));
        array_push($conversionValue, 1);
        array_push($isBase, 1);
        array_push($isPrimaryUnit, 1);
        array_push($productUnitsRemarks, $this->faker->sentence());

        $productArr = Product::factory()->make()->toArray();
        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($companyId_2),
            'code' => $company_1->products()->where('product_type', '=', ProductType::SERVICE->value)->inRandomOrder()->first()->code,
            'product_group_id' => Hashids::encode($company_2->productGroups()->where('category', '!=', ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company_2->brands()->inRandomOrder()->first()->id),
            'product_type' => $this->faker->numberBetween(1, 3),
            'product_units_hId' => $product_units_hId,
            'product_units_code' => $productUnitsCode,
            'product_units_unit_hId' => $unitId,
            'product_units_conv_value' => $conversionValue,
            'product_units_is_base' => $isBase,
            'product_units_is_primary_unit' => $isPrimaryUnit,
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

        $this->productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS_AND_SERVICES->value]);
        $this->brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);
        $this->unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS_AND_SERVICES->value]);
        $this->productSeeder->callWith(ProductTableSeeder::class, [3, $companyId, ProductCategory::PRODUCTS_AND_SERVICES->value]);

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