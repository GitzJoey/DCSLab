<?php

namespace Tests\Feature;

use App\Enums\ProductGroupCategory;
use App\Enums\ProductType;
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
use Illuminate\Foundation\Testing\WithFaker;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class ProductAPICreateTest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_product_api_call_store_product_expect_successful()
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

        $productGroup = $company->productGroups()
                            ->where('category', '=', ProductGroupCategory::PRODUCTS->value)
                            ->inRandomOrder()->first();

        $brand = $company->brands()->inRandomOrder()->first();

        $productArr = Product::factory()
            ->setProductTypeAsProduct()
            ->setStatusActive()
            ->make([
                'company_id' => Hashids::encode($company->id),
                'product_group_id' => Hashids::encode($productGroup->id),
                'brand_id' => Hashids::encode($brand->id),
            ])->toArray();

        $arr_product_unit_id = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $units = $company->units()
                    ->where('category', '=', UnitCategory::PRODUCTS->value)
                    ->inRandomOrder()->get()->shuffle();

        $productUnitCount = random_int(1, $units->count());
        $primaryUnitIdx = random_int(0, $productUnitCount - 1);
        for ($i = 0; $i < $productUnitCount; $i++) {
            $productUnit = ProductUnit::factory()
                            ->setConversionValue($i == 0 ? 1 : random_int(2, 10))
                            ->setIsPrimaryUnit($i == $primaryUnitIdx)
                            ->make()->toArray();

            array_push($arr_product_unit_id, '');
            array_push($arr_product_unit_code, $productUnit['code']);
            array_push($arr_product_unit_unit_id, Hashids::encode($units[$i]->id));
            array_push($arr_product_unit_conversion_value, $productUnit['conversion_value']);
            array_push($arr_product_unit_is_base, $productUnit['is_base']);
            array_push($arr_product_unit_is_primary_unit, $productUnit['is_primary_unit']);
            array_push($arr_product_unit_remarks, $productUnit['remarks']);
        }

        $productArr = array_merge($productArr, [
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

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

        for ($i = 0; $i < $productUnitCount; $i++) {
            $this->assertDatabaseHas('product_units', [
                'company_id' => Hashids::decode($productArr['company_id'])[0],
                'code' => $arr_product_unit_code[$i],
                'unit_id' => Hashids::decode($arr_product_unit_unit_id[$i])[0],
                'conversion_value' => $arr_product_unit_conversion_value[$i],
                'is_base' => $arr_product_unit_is_base[$i],
                'is_primary_unit' => $arr_product_unit_is_primary_unit[$i],
                'remarks' => $arr_product_unit_remarks[$i],
            ]);
        }
    }

    public function test_product_api_call_store_service_expect_successful()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(ProductGroup::factory()->setCategoryToService()->count(5))
                        ->has(Unit::factory()->setCategoryToService()->count(5))
                    )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productGroup = $company->productGroups()
                            ->where('category', '=', ProductGroupCategory::SERVICES->value)
                            ->inRandomOrder()->first();

        $productArr = Product::factory()
            ->setProductTypeAsService()
            ->setStatusActive()
            ->make([
                'company_id' => Hashids::encode($company->id),
                'product_group_id' => Hashids::encode($productGroup->id),
            ])->toArray();

        $arr_product_unit_id = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $unit = $company->units()
                    ->where('category', '=', UnitCategory::SERVICES->value)
                    ->inRandomOrder()->first();

        $productUnit = ProductUnit::factory()
                        ->setConversionValue(1)
                        ->setIsPrimaryUnit(true)
                        ->make()->toArray();

        array_push($arr_product_unit_id, '');
        array_push($arr_product_unit_code, $productUnit['code']);
        array_push($arr_product_unit_unit_id, Hashids::encode($unit->id));
        array_push($arr_product_unit_conversion_value, $productUnit['conversion_value']);
        array_push($arr_product_unit_is_base, $productUnit['is_base']);
        array_push($arr_product_unit_is_primary_unit, $productUnit['is_primary_unit']);
        array_push($arr_product_unit_remarks, $productUnit['remarks']);

        $productArr = array_merge($productArr, [
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('products', [
            'company_id' => Hashids::decode($productArr['company_id'])[0],
            'code' => $productArr['code'],
            'product_group_id' => Hashids::decode($productArr['product_group_id']),
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

        $this->assertDatabaseHas('product_units', [
            'company_id' => Hashids::decode($productArr['company_id'])[0],
            'code' => $arr_product_unit_code[0],
            'unit_id' => Hashids::decode($arr_product_unit_unit_id[0])[0],
            'conversion_value' => $arr_product_unit_conversion_value[0],
            'is_base' => $arr_product_unit_is_base[0],
            'is_primary_unit' => $arr_product_unit_is_primary_unit[0],
            'remarks' => $arr_product_unit_remarks[0],
        ]);
    }

    public function test_product_api_call_store_product_with_nonexistance_product_group_id_expect_failed()
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

        $brand = $company->brands()->inRandomOrder()->first();

        $productArr = Product::factory()
            ->setProductTypeAsProduct()
            ->setStatusActive()
            ->make([
                'company_id' => Hashids::encode($company->id),
                'product_group_id' => Hashids::encode(ProductGroup::max('id') + 1),
                'brand_id' => Hashids::encode($brand->id),
            ])->toArray();

        $arr_product_unit_id = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $units = $company->units()
                    ->where('category', '=', UnitCategory::PRODUCTS->value)
                    ->inRandomOrder()->get()->shuffle();

        $productUnitCount = random_int(1, $units->count());
        $primaryUnitIdx = random_int(0, $productUnitCount - 1);
        for ($i = 0; $i < $productUnitCount; $i++) {
            $productUnit = ProductUnit::factory()
                            ->setConversionValue($i == 0 ? 1 : random_int(2, 10))
                            ->setIsPrimaryUnit($i == $primaryUnitIdx)
                            ->make()->toArray();

            array_push($arr_product_unit_id, '');
            array_push($arr_product_unit_code, $productUnit['code']);
            array_push($arr_product_unit_unit_id, Hashids::encode($units[$i]->id));
            array_push($arr_product_unit_conversion_value, $productUnit['conversion_value']);
            array_push($arr_product_unit_is_base, $productUnit['is_base']);
            array_push($arr_product_unit_is_primary_unit, $productUnit['is_primary_unit']);
            array_push($arr_product_unit_remarks, $productUnit['remarks']);
        }

        $productArr = array_merge($productArr, [
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_store_product_with_nonexistance_brand_id_expect_failed()
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

        $productGroup = $company->productGroups()
                            ->where('category', '=', ProductGroupCategory::PRODUCTS->value)
                            ->inRandomOrder()->first();

        $productArr = Product::factory()
            ->setProductTypeAsProduct()
            ->setStatusActive()
            ->make([
                'company_id' => Hashids::encode($company->id),
                'product_group_id' => Hashids::encode($productGroup->id),
                'brand_id' => Hashids::encode(Brand::max('id') + 1),
            ])->toArray();

        $arr_product_unit_id = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $units = $company->units()
                    ->where('category', '=', UnitCategory::PRODUCTS->value)
                    ->inRandomOrder()->get()->shuffle();

        $productUnitCount = random_int(1, $units->count());
        $primaryUnitIdx = random_int(0, $productUnitCount - 1);
        for ($i = 0; $i < $productUnitCount; $i++) {
            $productUnit = ProductUnit::factory()
                            ->setConversionValue($i == 0 ? 1 : random_int(2, 10))
                            ->setIsPrimaryUnit($i == $primaryUnitIdx)
                            ->make()->toArray();

            array_push($arr_product_unit_id, '');
            array_push($arr_product_unit_code, $productUnit['code']);
            array_push($arr_product_unit_unit_id, Hashids::encode($units[$i]->id));
            array_push($arr_product_unit_conversion_value, $productUnit['conversion_value']);
            array_push($arr_product_unit_is_base, $productUnit['is_base']);
            array_push($arr_product_unit_is_primary_unit, $productUnit['is_primary_unit']);
            array_push($arr_product_unit_remarks, $productUnit['remarks']);
        }

        $productArr = array_merge($productArr, [
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_store_product_with_nonexistance_product_unit_unit_id_expect_failed()
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

        $productGroup = $company->productGroups()
                            ->where('category', '=', ProductGroupCategory::PRODUCTS->value)
                            ->inRandomOrder()->first();

        $brand = $company->brands()->inRandomOrder()->first();

        $productArr = Product::factory()
            ->setProductTypeAsProduct()
            ->setStatusActive()
            ->make([
                'company_id' => Hashids::encode($company->id),
                'product_group_id' => Hashids::encode($productGroup->id),
                'brand_id' => Hashids::encode($brand->id),
            ])->toArray();

        $arr_product_unit_id = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $units = $company->units()
                    ->where('category', '=', UnitCategory::PRODUCTS->value)
                    ->inRandomOrder()->get()->shuffle();

        $productUnitCount = random_int(1, $units->count());
        $primaryUnitIdx = random_int(0, $productUnitCount - 1);
        for ($i = 0; $i < $productUnitCount; $i++) {
            $productUnit = ProductUnit::factory()
                            ->setConversionValue($i == 0 ? 1 : random_int(2, 10))
                            ->setIsPrimaryUnit($i == $primaryUnitIdx)
                            ->make()->toArray();

            array_push($arr_product_unit_id, '');
            array_push($arr_product_unit_code, $productUnit['code']);
            array_push($arr_product_unit_unit_id, Hashids::encode($i == $productUnitCount - 1 ? Unit::max('id') + 1 : $units[$i]->id));
            array_push($arr_product_unit_conversion_value, $productUnit['conversion_value']);
            array_push($arr_product_unit_is_base, $productUnit['is_base']);
            array_push($arr_product_unit_is_primary_unit, $productUnit['is_primary_unit']);
            array_push($arr_product_unit_remarks, $productUnit['remarks']);
        }

        $productArr = array_merge($productArr, [
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_store_product_with_existing_code_in_same_company_expect_failed()
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

        $productGroup = $company->productGroups()
                            ->where('category', '=', ProductGroupCategory::PRODUCTS->value)
                            ->inRandomOrder()->first();

        $brand = $company->brands()->inRandomOrder()->first();

        $productArr = Product::factory()
            ->setProductTypeAsProduct()
            ->setStatusActive()
            ->make([
                'company_id' => Hashids::encode($company->id),
                'code' => $company->products()->inRandomOrder()->first()->code,
                'product_group_id' => Hashids::encode($productGroup->id),
                'brand_id' => Hashids::encode($brand->id),
            ])->toArray();

        $arr_product_unit_id = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $units = $company->units()
                    ->where('category', '=', UnitCategory::PRODUCTS->value)
                    ->inRandomOrder()->get()->shuffle();

        $productUnitCount = random_int(1, $units->count());
        $primaryUnitIdx = random_int(0, $productUnitCount - 1);
        for ($i = 0; $i < $productUnitCount; $i++) {
            $productUnit = ProductUnit::factory()
                            ->setConversionValue($i == 0 ? 1 : random_int(2, 10))
                            ->setIsPrimaryUnit($i == $primaryUnitIdx)
                            ->make()->toArray();

            array_push($arr_product_unit_id, '');
            array_push($arr_product_unit_code, $productUnit['code']);
            array_push($arr_product_unit_unit_id, Hashids::encode($units[$i]->id));
            array_push($arr_product_unit_conversion_value, $productUnit['conversion_value']);
            array_push($arr_product_unit_is_base, $productUnit['is_base']);
            array_push($arr_product_unit_is_primary_unit, $productUnit['is_primary_unit']);
            array_push($arr_product_unit_remarks, $productUnit['remarks']);
        }

        $productArr = array_merge($productArr, [
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_store_service_with_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(ProductGroup::factory()->setCategoryToService()->count(5))
                        ->has(Unit::factory()->setCategoryToService()->count(5))
                    )->create();

        $this->actingAs($user);

        $company = $user->companies->first();

        for ($i = 0; $i < 3; $i++) {
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
        }

        $productGroup = $company->productGroups()
                            ->where('category', '=', ProductGroupCategory::SERVICES->value)
                            ->inRandomOrder()->first();

        $productArr = Product::factory()
                        ->setProductTypeAsService()
                        ->setStatusActive()
                        ->make([
                            'company_id' => Hashids::encode($company->id),
                            'code' => $company->products()->where('product_type', '=', ProductType::SERVICE->value)->inRandomOrder()->first()->code,
                            'product_group_id' => Hashids::encode($productGroup->id),
                        ])->toArray();

        $arr_product_unit_id = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $unit = $company->units()
                    ->where('category', '=', UnitCategory::SERVICES->value)
                    ->inRandomOrder()->first();

        $productUnit = ProductUnit::factory()
                        ->setConversionValue(1)
                        ->setIsPrimaryUnit(true)
                        ->make()->toArray();

        array_push($arr_product_unit_id, '');
        array_push($arr_product_unit_code, $productUnit['code']);
        array_push($arr_product_unit_unit_id, Hashids::encode($unit->id));
        array_push($arr_product_unit_conversion_value, $productUnit['conversion_value']);
        array_push($arr_product_unit_is_base, $productUnit['is_base']);
        array_push($arr_product_unit_is_primary_unit, $productUnit['is_primary_unit']);
        array_push($arr_product_unit_remarks, $productUnit['remarks']);

        $productArr = array_merge($productArr, [
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_store_with_existing_code_in_different_company_expect_successful()
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

        $companies = $user->companies()->inRandomOrder()->take(2)->get();

        $company_1 = $companies[0];

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

        $company_2 = $companies[1];

        $productGroup = $company_2->productGroups()
                            ->where('category', '=', ProductGroupCategory::PRODUCTS->value)
                            ->inRandomOrder()->first();

        $brand = $company_2->brands()->inRandomOrder()->first();

        $productArr = Product::factory()
                        ->setProductTypeAsProduct()
                        ->setStatusActive()
                        ->make([
                            'company_id' => Hashids::encode($company_2->id),
                            'code' => $company_1->products()->inRandomOrder()->first()->code,
                            'product_group_id' => Hashids::encode($productGroup->id),
                            'brand_id' => Hashids::encode($brand->id),
                        ])->toArray();

        $arr_product_unit_id = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $units = $company_2->units()
                    ->where('category', '=', UnitCategory::PRODUCTS->value)
                    ->inRandomOrder()->get()->shuffle();

        $productUnitCount = random_int(1, $units->count());
        $primaryUnitIdx = random_int(0, $productUnitCount - 1);
        for ($i = 0; $i < $productUnitCount; $i++) {
            $productUnit = ProductUnit::factory()
                                ->setConversionValue($i == 0 ? 1 : random_int(2, 10))
                                ->setIsPrimaryUnit($i == $primaryUnitIdx)
                                ->make()->toArray();

            array_push($arr_product_unit_id, '');
            array_push($arr_product_unit_code, $productUnit['code']);
            array_push($arr_product_unit_unit_id, Hashids::encode($units[$i]->id));
            array_push($arr_product_unit_conversion_value, $productUnit['conversion_value']);
            array_push($arr_product_unit_is_base, $productUnit['is_base']);
            array_push($arr_product_unit_is_primary_unit, $productUnit['is_primary_unit']);
            array_push($arr_product_unit_remarks, $productUnit['remarks']);
        }

        $productArr = array_merge($productArr, [
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('products', [
            'company_id' => $company_2->id,
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

        for ($i = 0; $i < $productUnitCount; $i++) {
            $this->assertDatabaseHas('product_units', [
                'company_id' => $company_2->id,
                'code' => $arr_product_unit_code[$i],
                'unit_id' => Hashids::decode($arr_product_unit_unit_id[$i])[0],
                'conversion_value' => $arr_product_unit_conversion_value[$i],
                'is_base' => $arr_product_unit_is_base[$i],
                'is_primary_unit' => $arr_product_unit_is_primary_unit[$i],
                'remarks' => $arr_product_unit_remarks[$i],
            ]);
        }
    }

    public function test_product_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(ProductGroup::factory()->setCategoryToService()->count(5))
                        ->has(Unit::factory()->setCategoryToService()->count(5))
                    )->create();

        $this->actingAs($user);

        $productArr = [];
        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertJsonValidationErrors(['company_id', 'code', 'name']);
    }
}
