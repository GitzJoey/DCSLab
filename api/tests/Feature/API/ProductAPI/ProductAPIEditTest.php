<?php

namespace Tests\Feature\API\ProductAPI;

use App\Enums\ProductCategory;
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
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class ProductAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_product_api_call_update_product_and_insert_product_units_expect_db_updated()
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

        $productArr = $product->toArray();

        $arr_product_unit_id = [];
        $arr_product_unit_ulid = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $productUnits = $product->productUnits;
        foreach ($productUnits as $productUnit) {
            array_push($arr_product_unit_id, Hashids::encode($productUnit->id));
            array_push($arr_product_unit_ulid, $productUnit->ulid);
            array_push($arr_product_unit_code, $productUnit->code);
            array_push($arr_product_unit_unit_id, Hashids::encode($productUnit->unit_id));
            array_push($arr_product_unit_conversion_value, $productUnit->conversion_value);
            array_push($arr_product_unit_is_base, $productUnit->is_base);
            array_push($arr_product_unit_is_primary_unit, $productUnit->is_primary_unit);
            array_push($arr_product_unit_remarks, $productUnit->remarks);
        }

        $productUnit = ProductUnit::factory()->make();
        array_push($arr_product_unit_id, '');
        array_push($arr_product_unit_ulid, '');
        array_push($arr_product_unit_code, $productUnit->code);
        array_push($arr_product_unit_unit_id, Hashids::encode($company->units()->where('category', '=', ProductCategory::PRODUCTS->value)->inRandomOrder()->first()->id));
        array_push($arr_product_unit_conversion_value, $productUnits[count($productUnits) - 1]['conversion_value'] * 2);
        array_push($arr_product_unit_is_base, false);
        array_push($arr_product_unit_is_primary_unit, false);
        array_push($arr_product_unit_remarks, $productUnit->remarks);

        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($company->id),
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '=', ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_ulid' => $arr_product_unit_ulid,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->ulid), $productArr);

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

        foreach ($productUnits as $productUnit) {
            $this->assertDatabaseHas('product_units', [
                'company_id' => $company->id,
                'product_id' => $product->id,
                'unit_id' => $productUnit->unit_id,
                'code' => $productUnit->code,
                'is_base' => $productUnit->is_base,
                'conversion_value' => $productUnit->conversion_value,
                'is_primary_unit' => $productUnit->is_primary_unit,
                'remarks' => $productUnit->remarks,
            ]);
        }
    }

    public function test_product_api_call_update_product_with_nonexistance_product_group_id_expect_failed()
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

        $productArr = $product->toArray();

        $arr_product_unit_id = [];
        $arr_product_unit_ulid = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $productUnits = $product->productUnits;
        foreach ($productUnits as $productUnit) {
            array_push($arr_product_unit_id, Hashids::encode($productUnit->id));
            array_push($arr_product_unit_ulid, $productUnit->ulid);
            array_push($arr_product_unit_code, $productUnit->code);
            array_push($arr_product_unit_unit_id, Hashids::encode($productUnit->unit_id));
            array_push($arr_product_unit_conversion_value, $productUnit->conversion_value);
            array_push($arr_product_unit_is_base, $productUnit->is_base);
            array_push($arr_product_unit_is_primary_unit, $productUnit->is_primary_unit);
            array_push($arr_product_unit_remarks, $productUnit->remarks);
        }

        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($company->id),
            'product_group_id' => Hashids::encode(ProductGroup::max('id') + 1),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_ulid' => $arr_product_unit_ulid,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->ulid), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_update_product_with_nonexistance_brand_id_expect_failed()
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

        $productArr = $product->toArray();

        $arr_product_unit_id = [];
        $arr_product_unit_ulid = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $productUnits = $product->productUnits;
        foreach ($productUnits as $productUnit) {
            array_push($arr_product_unit_id, Hashids::encode($productUnit->id));
            array_push($arr_product_unit_ulid, $productUnit->ulid);
            array_push($arr_product_unit_code, $productUnit->code);
            array_push($arr_product_unit_unit_id, Hashids::encode($productUnit->unit_id));
            array_push($arr_product_unit_conversion_value, $productUnit->conversion_value);
            array_push($arr_product_unit_is_base, $productUnit->is_base);
            array_push($arr_product_unit_is_primary_unit, $productUnit->is_primary_unit);
            array_push($arr_product_unit_remarks, $productUnit->remarks);
        }

        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($company->id),
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '=', ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode(Brand::max('id') + 1),
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_ulid' => $arr_product_unit_ulid,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->ulid), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_update_product_with_nonexistance_product_unit_unit_id_expect_failed()
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

        $productArr = $product->toArray();

        $arr_product_unit_id = [];
        $arr_product_unit_ulid = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $productUnits = $product->productUnits;
        foreach ($productUnits as $productUnit) {
            array_push($arr_product_unit_id, Hashids::encode($productUnit->id));
            array_push($arr_product_unit_ulid, $productUnit->ulid);
            array_push($arr_product_unit_code, $productUnit->code);
            array_push($arr_product_unit_unit_id, Hashids::encode($company->units()->where('category', '=', ProductCategory::PRODUCTS->value)->inRandomOrder()->first()->id));
            array_push($arr_product_unit_conversion_value, $productUnit->conversion_value);
            array_push($arr_product_unit_is_base, $productUnit->is_base);
            array_push($arr_product_unit_is_primary_unit, $productUnit->is_primary_unit);
            array_push($arr_product_unit_remarks, $productUnit->remarks);
        }

        $lastRow = count($arr_product_unit_id) - 1;
        $arr_product_unit_id[$lastRow] = '';
        $arr_product_unit_ulid[$lastRow] = '';
        $arr_product_unit_code[$lastRow] = ProductUnit::factory()->make()->code;
        $arr_product_unit_unit_id[$lastRow] = Hashids::encode(Unit::max('id') + 1);
        $arr_product_unit_conversion_value[$lastRow] = $productUnits[count($productUnits) - 1]['conversion_value'] * 2;
        $arr_product_unit_is_base[$lastRow] = false;
        $arr_product_unit_is_primary_unit[$lastRow] = false;
        $arr_product_unit_remarks[$lastRow] = ProductUnit::factory()->make()->remarks;

        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($company->id),
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '=', ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_ulid' => $arr_product_unit_ulid,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->ulid), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_update_product_and_insert_product_units_with_nonexistance_product_unit_unit_id_expect_failed()
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

        $arr_product_unit_id = [];
        $arr_product_unit_ulid = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $productUnits = $product->productUnits;
        foreach ($productUnits as $productUnit) {
            array_push($arr_product_unit_id, Hashids::encode($productUnit->id));
            array_push($arr_product_unit_ulid, $productUnit->ulid);
            array_push($arr_product_unit_code, $productUnit->code);
            array_push($arr_product_unit_unit_id, Hashids::encode($productUnit->unit_id));
            array_push($arr_product_unit_conversion_value, $productUnit->conversion_value);
            array_push($arr_product_unit_is_base, $productUnit->is_base);
            array_push($arr_product_unit_is_primary_unit, $productUnit->is_primary_unit);
            array_push($arr_product_unit_remarks, $productUnit->remarks);
        }

        $productUnit = ProductUnit::factory()->make();
        array_push($arr_product_unit_id, '');
        array_push($arr_product_unit_ulid, '');
        array_push($arr_product_unit_code, $productUnit->code);
        array_push($arr_product_unit_unit_id, Hashids::encode(Unit::max('id') + 1));
        array_push($arr_product_unit_conversion_value, $productUnits[count($productUnits) - 1]['conversion_value'] * 2);
        array_push($arr_product_unit_is_base, false);
        array_push($arr_product_unit_is_primary_unit, false);
        array_push($arr_product_unit_remarks, $productUnit->remarks);

        $productArr = $product->toArray();

        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($company->id),
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '=', ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_ulid' => $arr_product_unit_ulid,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->ulid), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_update_product_and_insert_product_units_with_non_numeric_conv_code_expect_failed()
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

        $productArr = $product->toArray();

        $arr_product_unit_id = [];
        $arr_product_unit_ulid = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $productUnits = $product->productUnits;
        foreach ($productUnits as $productUnit) {
            array_push($arr_product_unit_id, Hashids::encode($productUnit->id));
            array_push($arr_product_unit_ulid, $productUnit->ulid);
            array_push($arr_product_unit_code, $productUnit->code);
            array_push($arr_product_unit_unit_id, Hashids::encode($productUnit->unit_id));
            array_push($arr_product_unit_conversion_value, $productUnit->conversion_value);
            array_push($arr_product_unit_is_base, $productUnit->is_base);
            array_push($arr_product_unit_is_primary_unit, $productUnit->is_primary_unit);
            array_push($arr_product_unit_remarks, $productUnit->remarks);
        }

        $productUnit = ProductUnit::factory()->make();
        array_push($arr_product_unit_id, '');
        array_push($arr_product_unit_ulid, '');
        array_push($arr_product_unit_code, $productUnit->code);
        array_push($arr_product_unit_unit_id, Hashids::encode($company->units()->where('category', '=', ProductCategory::PRODUCTS->value)->inRandomOrder()->first()->id));
        array_push($arr_product_unit_conversion_value, 'test');
        array_push($arr_product_unit_is_base, false);
        array_push($arr_product_unit_is_primary_unit, false);
        array_push($arr_product_unit_remarks, $productUnit->remarks);

        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($company->id),
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '=', ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_ulid' => $arr_product_unit_ulid,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->ulid), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_update_product_and_edit_product_units_expect_db_updated()
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

        $productArr = $product->toArray();

        $arr_product_unit_id = [];
        $arr_product_unit_ulid = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $productUnits = $product->productUnits;
        foreach ($productUnits as $productUnit) {
            array_push($arr_product_unit_id, Hashids::encode($productUnit->id));
            array_push($arr_product_unit_ulid, $productUnit->ulid);
            array_push($arr_product_unit_code, $productUnit->code);
            array_push($arr_product_unit_unit_id, Hashids::encode($productUnit->unit_id));
            array_push($arr_product_unit_conversion_value, $productUnit->conversion_value);
            array_push($arr_product_unit_is_base, $productUnit->is_base);
            array_push($arr_product_unit_is_primary_unit, $productUnit->is_primary_unit);
            array_push($arr_product_unit_remarks, $productUnit->remarks);
        }

        $lastRow = count($arr_product_unit_id) - 1;
        $arr_product_unit_id[$lastRow] = '';
        $arr_product_unit_ulid[$lastRow] = '';
        $arr_product_unit_code[$lastRow] = ProductUnit::factory()->make()->code;
        $arr_product_unit_unit_id[$lastRow] = Hashids::encode($company->units()->where('category', '=', ProductCategory::PRODUCTS->value)->inRandomOrder()->first()->id);
        $arr_product_unit_conversion_value[$lastRow] = $productUnits[count($productUnits) - 1]['conversion_value'] * 2;
        $arr_product_unit_is_base[$lastRow] = false;
        $arr_product_unit_is_primary_unit[$lastRow] = false;
        $arr_product_unit_remarks[$lastRow] = ProductUnit::factory()->make()->remarks;

        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($company->id),
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '=', ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_ulid' => $arr_product_unit_ulid,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->ulid), $productArr);

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

        for ($i = 0; $i < count($arr_product_unit_code); $i++) {
            $this->assertDatabaseHas('product_units', [
                'company_id' => $company->id,
                'unit_id' => Hashids::decode($arr_product_unit_unit_id[$i])[0],
                'code' => $arr_product_unit_code[$i],
                'is_base' => $arr_product_unit_is_base[$i],
                'conversion_value' => $arr_product_unit_conversion_value[$i],
                'is_primary_unit' => $arr_product_unit_is_primary_unit[$i],
                'remarks' => $arr_product_unit_remarks[$i],
            ]);
        }
    }

    public function test_product_api_call_update_product_and_edit_product_units_with_invalid_ulid_expect_failed()
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

        $productArr = $product->toArray();

        $arr_product_unit_id = [];
        $arr_product_unit_ulid = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $productUnits = $product->productUnits;
        foreach ($productUnits as $productUnit) {
            array_push($arr_product_unit_id, Hashids::encode($productUnit->id));
            array_push($arr_product_unit_ulid, $productUnit->ulid);
            array_push($arr_product_unit_code, $productUnit->code);
            array_push($arr_product_unit_unit_id, Hashids::encode($productUnit->unit_id));
            array_push($arr_product_unit_conversion_value, $productUnit->conversion_value);
            array_push($arr_product_unit_is_base, $productUnit->is_base);
            array_push($arr_product_unit_is_primary_unit, $productUnit->is_primary_unit);
            array_push($arr_product_unit_remarks, $productUnit->remarks);
        }

        $lastRow = count($arr_product_unit_id) - 1;
        $arr_product_unit_id[$lastRow] = $arr_product_unit_id[$lastRow];
        $arr_product_unit_ulid[$lastRow] = 'TEST123TEST123';
        $arr_product_unit_code[$lastRow] = ProductUnit::factory()->make()->code;
        $arr_product_unit_unit_id[$lastRow] = Hashids::encode($company->units()->where('category', '=', ProductCategory::PRODUCTS->value)->inRandomOrder()->first()->id);
        $arr_product_unit_conversion_value[$lastRow] = $productUnits[count($productUnits) - 1]['conversion_value'] * 2;
        $arr_product_unit_is_base[$lastRow] = false;
        $arr_product_unit_is_primary_unit[$lastRow] = false;
        $arr_product_unit_remarks[$lastRow] = ProductUnit::factory()->make()->remarks;

        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($company->id),
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '=', ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_ulid' => $arr_product_unit_ulid,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->ulid), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_update_product_and_delete_product_units_expect_db_updated()
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

        $productUnitCount = random_int(2, $units->count());
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

        $productArr = $product->toArray();

        $arr_product_unit_id = [];
        $arr_product_unit_ulid = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $productUnits = $product->productUnits;
        foreach ($productUnits as $productUnit) {
            array_push($arr_product_unit_id, Hashids::encode($productUnit->id));
            array_push($arr_product_unit_ulid, $productUnit->ulid);
            array_push($arr_product_unit_code, $productUnit->code);
            array_push($arr_product_unit_unit_id, Hashids::encode($productUnit->unit_id));
            array_push($arr_product_unit_conversion_value, $productUnit->conversion_value);
            array_push($arr_product_unit_is_base, $productUnit->is_base);
            array_push($arr_product_unit_is_primary_unit, $productUnit->is_primary_unit);
            array_push($arr_product_unit_remarks, $productUnit->remarks);
        }

        array_pop($arr_product_unit_id);
        array_pop($arr_product_unit_ulid);
        array_pop($arr_product_unit_code);
        array_pop($arr_product_unit_unit_id);
        array_pop($arr_product_unit_conversion_value);
        array_pop($arr_product_unit_is_base);
        array_pop($arr_product_unit_is_primary_unit);
        array_pop($arr_product_unit_remarks);

        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($company->id),
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '=', ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_ulid' => $arr_product_unit_ulid,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->ulid), $productArr);

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

        for ($i = 0; $i < count($arr_product_unit_code); $i++) {
            $this->assertDatabaseHas('product_units', [
                'company_id' => $company->id,
                'unit_id' => Hashids::decode($arr_product_unit_unit_id[$i])[0],
                'code' => $arr_product_unit_code[$i],
                'is_base' => $arr_product_unit_is_base[$i],
                'conversion_value' => $arr_product_unit_conversion_value[$i],
                'is_primary_unit' => $arr_product_unit_is_primary_unit[$i],
                'remarks' => $arr_product_unit_remarks[$i],
            ]);
        }
    }

    public function test_product_api_call_update_product_and_use_existing_code_in_same_company_expect_failed()
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

        $product = $company->products()->inRandomOrder()->first();
        $productArr = $product->toArray();

        $arr_product_unit_id = [];
        $arr_product_unit_ulid = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $productUnits = $product->productUnits;
        foreach ($productUnits as $productUnit) {
            array_push($arr_product_unit_id, Hashids::encode($productUnit->id));
            array_push($arr_product_unit_ulid, $productUnit->ulid);
            array_push($arr_product_unit_code, $productUnit->code);
            array_push($arr_product_unit_unit_id, Hashids::encode($productUnit->unit_id));
            array_push($arr_product_unit_conversion_value, $productUnit->conversion_value);
            array_push($arr_product_unit_is_base, $productUnit->is_base);
            array_push($arr_product_unit_is_primary_unit, $productUnit->is_primary_unit);
            array_push($arr_product_unit_remarks, $productUnit->remarks);
        }

        $productUnit = ProductUnit::factory()->make();
        array_push($arr_product_unit_id, '');
        array_push($arr_product_unit_ulid, '');
        array_push($arr_product_unit_code, $productUnit->code);
        array_push($arr_product_unit_unit_id, Hashids::encode($company->units()->where('category', '=', ProductCategory::PRODUCTS->value)->inRandomOrder()->first()->id));
        array_push($arr_product_unit_conversion_value, $productUnits[count($productUnits) - 1]['conversion_value'] * 2);
        array_push($arr_product_unit_is_base, false);
        array_push($arr_product_unit_is_primary_unit, false);
        array_push($arr_product_unit_remarks, $productUnit->remarks);

        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($company->id),
            'code' => $company->products()->where('id', '!=', $product->id)->inRandomOrder()->first()->code,
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '=', ProductGroupCategory::PRODUCTS->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company->brands()->inRandomOrder()->first()->id),
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_ulid' => $arr_product_unit_ulid,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->ulid), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_update_service_and_use_existing_code_in_same_company_expect_failed()
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

            $service->create();
        }

        $service = $company->products()
            ->where('product_type', '=', ProductType::SERVICE->value)
            ->inRandomOrder()->first();

        $serviceArr = $service->toArray();

        $arr_product_unit_id = [];
        $arr_product_unit_ulid = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        array_push($arr_product_unit_id, '');
        array_push($arr_product_unit_ulid, '');
        array_push($arr_product_unit_code, ProductUnit::factory()->make()->code);
        array_push($arr_product_unit_unit_id, Hashids::encode($company->units()->where('category', '=', ProductCategory::SERVICES->value)->inRandomOrder()->first()->id));
        array_push($arr_product_unit_conversion_value, 1);
        array_push($arr_product_unit_is_base, 1);
        array_push($arr_product_unit_is_primary_unit, 1);
        array_push($arr_product_unit_remarks, ProductUnit::factory()->make()->remarks);

        $newCode = $company->products()->where([
            ['id', '!=', $service->id],
            ['product_type', '=', ProductType::SERVICE->value],
        ])->inRandomOrder()->first()->code;

        $serviceArr = array_merge($serviceArr, [
            'company_id' => Hashids::encode($company->id),
            'code' => $newCode,
            'product_group_id' => Hashids::encode($company->productGroups()->where('category', '=', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first()->id),
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_ulid' => $arr_product_unit_ulid,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.edit', $service->ulid), $serviceArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_update_product_and_use_existing_code_in_different_company_expect_successful()
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

        for ($i = 0; $i < 3; $i++) {
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

        $company_2 = $user->companies[1];

        for ($i = 0; $i < 3; $i++) {
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

        $product = $company_1->products()->inRandomOrder()->first();

        $arr_product_unit_id = [];
        $arr_product_unit_ulid = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $productUnits = $product->productUnits;
        foreach ($productUnits as $productUnit) {
            array_push($arr_product_unit_id, Hashids::encode($productUnit->id));
            array_push($arr_product_unit_ulid, $productUnit->ulid);
            array_push($arr_product_unit_code, $productUnit->code);
            array_push($arr_product_unit_unit_id, Hashids::encode($productUnit->unit_id));
            array_push($arr_product_unit_conversion_value, $productUnit->conversion_value);
            array_push($arr_product_unit_is_base, $productUnit->is_base);
            array_push($arr_product_unit_is_primary_unit, $productUnit->is_primary_unit);
            array_push($arr_product_unit_remarks, $productUnit->remarks);
        }

        $productArr = $product->toArray();
        $productArr = array_merge($productArr, [
            'company_id' => Hashids::encode($company_1->id),
            'code' => $company_2->products()->inRandomOrder()->first()->id,
            'product_group_id' => Hashids::encode($company_1->productGroups()->where('category', '!=', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first()->id),
            'brand_id' => Hashids::encode($company_1->brands()->inRandomOrder()->first()->id),
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_ulid' => $arr_product_unit_ulid,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->ulid), $productArr);

        $api->assertSuccessful();
    }

    public function test_product_api_call_update_service_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory()->setCategoryToService()->count(5))
                ->has(Unit::factory()->setCategoryToService()->count(5)))
            ->has(Company::factory()->setStatusActive()
                ->has(ProductGroup::factory()->setCategoryToService()->count(5))
                ->has(Unit::factory()->setCategoryToService()->count(5)))
            ->create();

        $this->actingAs($user);

        $company_1 = $user->companies[0];
        for ($i = 0; $i < 3; $i++) {
            $productGroup = $company_1->productGroups()
                ->where('category', '=', ProductGroupCategory::SERVICES->value)
                ->inRandomOrder()->first();

            $service = Product::factory()
                ->for($company_1)
                ->for($productGroup)
                ->setProductTypeAsService();

            $unit = $company_1->units()->where('category', '=', UnitCategory::SERVICES->value)
                ->inRandomOrder()->first();

            $service = $service->has(
                ProductUnit::factory()
                    ->for($company_1)->for($unit)
                    ->setConversionValue(1)
                    ->setIsPrimaryUnit(true)
            );

            $service->create();
        }

        $company_2 = $user->companies[1];
        for ($i = 0; $i < 3; $i++) {
            $productGroup = $company_2->productGroups()
                ->where('category', '=', ProductGroupCategory::SERVICES->value)
                ->inRandomOrder()->first();

            $service = Product::factory()
                ->for($company_2)
                ->for($productGroup)
                ->setProductTypeAsService();

            $unit = $company_2->units()->where('category', '=', UnitCategory::SERVICES->value)
                ->inRandomOrder()->first();

            $service = $service->has(
                ProductUnit::factory()
                    ->for($company_2)->for($unit)
                    ->setConversionValue(1)
                    ->setIsPrimaryUnit(true)
            );

            $service->create();
        }

        $service = $company_1->products()->inRandomOrder()->first();

        $arr_product_unit_id = [];
        $arr_product_unit_ulid = [];
        $arr_product_unit_code = [];
        $arr_product_unit_unit_id = [];
        $arr_product_unit_conversion_value = [];
        $arr_product_unit_is_base = [];
        $arr_product_unit_is_primary_unit = [];
        $arr_product_unit_remarks = [];

        $productUnits = $service->productUnits;
        foreach ($productUnits as $productUnit) {
            array_push($arr_product_unit_id, Hashids::encode($productUnit->id));
            array_push($arr_product_unit_ulid, $productUnit->ulid);
            array_push($arr_product_unit_code, $productUnit->code);
            array_push($arr_product_unit_unit_id, Hashids::encode($productUnit->unit_id));
            array_push($arr_product_unit_conversion_value, $productUnit->conversion_value);
            array_push($arr_product_unit_is_base, $productUnit->is_base);
            array_push($arr_product_unit_is_primary_unit, $productUnit->is_primary_unit);
            array_push($arr_product_unit_remarks, $productUnit->remarks);
        }

        $serviceArr = $service->toArray();
        $serviceArr = array_merge($serviceArr, [
            'company_id' => Hashids::encode($company_1->id),
            'code' => $company_2->products()->where('product_type', '=', ProductType::SERVICE->value)->inRandomOrder()->first()->code,
            'product_group_id' => Hashids::encode($company_1->productGroups()->where('category', '=', ProductGroupCategory::SERVICES->value)->inRandomOrder()->first()->id),
            'arr_product_unit_id' => $arr_product_unit_id,
            'arr_product_unit_ulid' => $arr_product_unit_ulid,
            'arr_product_unit_code' => $arr_product_unit_code,
            'arr_product_unit_unit_id' => $arr_product_unit_unit_id,
            'arr_product_unit_conversion_value' => $arr_product_unit_conversion_value,
            'arr_product_unit_is_base' => $arr_product_unit_is_base,
            'arr_product_unit_is_primary_unit' => $arr_product_unit_is_primary_unit,
            'arr_product_unit_remarks' => $arr_product_unit_remarks,
        ]);

        $api = $this->json('POST', route('api.post.db.product.product.edit', $service->ulid), $serviceArr);

        $api->assertSuccessful();
    }
}
