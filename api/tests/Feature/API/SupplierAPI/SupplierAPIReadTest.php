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
use App\Models\Profile;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\SupplierProduct;
use App\Models\Unit;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class SupplierAPIReadTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_supplier_api_call_read_any_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault()
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

        $api = $this->getJson(route('api.get.db.supplier.supplier.read_any', [
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'paginate' => true,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertStatus(401);
    }

    public function test_supplier_api_call_read_any_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setIsDefault()
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

        $api = $this->getJson(route('api.get.db.supplier.supplier.read_any', [
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'paginate' => true,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertStatus(403);
    }

    public function test_supplier_api_call_read_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault()
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

        $api = $this->getJson(route('api.get.db.supplier.supplier.read', $supplier->ulid));

        $api->assertStatus(401);
    }

    public function test_supplier_api_call_read_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setIsDefault()
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

        $api = $this->getJson(route('api.get.db.supplier.supplier.read', $supplier->ulid));

        $api->assertStatus(403);
    }

    public function test_supplier_api_call_read_with_sql_injection_expect_injection_ignored()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault()
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

        $injections = [
            "' OR '1'='1",
            '1 UNION SELECT username, password FROM users',
            '1; DROP TABLE users',
            "' OR '1'='1' --",
            "' OR \'1\'=\'1",
            '1 OR SLEEP(5)',
            '1 AND (SELECT COUNT(*) FROM sysobjects) > 1',
            "1 AND (SELECT * FROM users WHERE username = 'admin' AND SLEEP(5))",
            "1; INSERT INTO logs (message) VALUES ('Injected SQL query')",
            "SELECT * FROM users; INSERT INTO logs (message) VALUES ('Injected SQL query')",
            "1 OR EXISTS(SELECT * FROM users WHERE username = 'admin' AND password LIKE '%a%')",
            "1; UPDATE users SET password = 'hacked' WHERE id = 1; --",
            '1 OR 1=1; DROP TABLE users; --',
            '1 AND 1=0 UNION ALL SELECT table_name, column_name FROM information_schema.columns',
            '1 AND 1=0 UNION ALL SELECT table_name, column_name FROM information_schema.columns WHERE table_schema = database()',
            "1; EXEC xp_cmdshell('echo vulnerable'); --",
            "' OR EXISTS(SELECT * FROM information_schema.tables WHERE table_schema='public' AND table_name='users' LIMIT 1) --",
            "1'; EXEC sp_addrolemember 'db_owner', 'admin'; --",
            "1' OR '1'='1'; -- EXEC master..xp_cmdshell 'echo vulnerable' --",
            "1' UNION ALL SELECT NULL, NULL, NULL, NULL, NULL, NULL, CONCAT(username, ':', password) FROM users --",
            '1; SELECT pg_sleep(5); --',
            "1 AND SLEEP(5) AND 'abc'='abc",
            "1 AND SLEEP(5) AND 'xyz'='xyz",
            '1 OR 1=1; SELECT COUNT(*) FROM information_schema.tables;',
            "1' UNION ALL SELECT table_name, column_name FROM information_schema.columns WHERE table_schema = 'public' --",
            '1 AND (SELECT * FROM (SELECT(SLEEP(5)))hOKz)',
            "1' AND 1=(SELECT COUNT(*) FROM tabname); --",
            "1'; WAITFOR DELAY '0:0:5' --",
            "1 OR 1=1; WAITFOR DELAY '0:0:5' --",
            "1; DECLARE @v VARCHAR(8000);SET @v = '';SELECT @v = @v + name + ', ' FROM sysobjects WHERE xtype = 'U';SELECT @v --",
            "1; SELECT COUNT(*), CONCAT(table_name, ':', column_name) FROM information_schema.columns GROUP BY table_name, column_name HAVING COUNT(*) > 1; --",
            '1; SELECT COUNT(*), table_name FROM information_schema.columns GROUP BY table_name HAVING COUNT(*) > 1; --',
            "1' OR '1'='1'; SELECT COUNT(*) FROM information_schema.tables; --",
            '1 AND (SELECT COUNT(*) FROM users) > 10',
            '1 AND (SELECT COUNT(*) FROM users) > 100',
            "1 OR EXISTS(SELECT * FROM users WHERE username = 'admin')",
            "1' OR EXISTS(SELECT * FROM users WHERE username = 'admin') OR '1'='1",
            "1' OR EXISTS(SELECT * FROM users WHERE username = 'admin') OR 'x'='x",
            '1 AND (SELECT COUNT(*) FROM users) > 1; SELECT * FROM users;',
            '1 OR 1=1; SELECT * FROM users;',
            "1' OR 1=1; SELECT * FROM users;",
            "1 OR 1=1; SELECT * FROM users WHERE username = 'admin'; --",
            "1' OR 1=1; SELECT * FROM users WHERE username = 'admin'; --",
            "1 OR 1=1; SELECT * FROM users WHERE username = 'admin' --",
            "1' OR 1=1; SELECT * FROM users WHERE username = 'admin' --",
            "' OR 1=1 --",
            "admin'--",
            "admin' #",
            "' OR 'x'='x",
            "' OR 'a'='a'",
            "' OR 'a'='a'--",
            "' OR 1=1",
            "' OR 1=1--",
            "' OR 1=1#",
            "' OR 1=1 /*",
            "' OR '1'='1'--",
            "' OR '1'='1'/*",
            "' OR '1'='1' #",
            "' OR '1'='1' /*",
            "' OR '1'='1' or ''='",
            "' OR '1'='1' or 'a'='a",
            "' OR '1'='1' or 'a'='a'--",
            "' OR '1'='1' or 'a'='a'/*",
            "' OR '1'='1' or 'a'='a' #",
            "' OR '1'='1' or 'a'='a' /*",
            '1; SELECT * FROM users WHERE 1=1',
            '1; SELECT * FROM users WHERE 1=1--',
            '1; SELECT * FROM users WHERE 1=1/*',
            "1' OR 1=1; SELECT * FROM users WHERE 1=1",
            "1' OR 1=1; SELECT * FROM users WHERE 1=1--",
            "1' OR 1=1; SELECT * FROM users WHERE 1=1/*",
            "1 OR '1'='1'; SELECT * FROM users WHERE 1=1",
            "1 OR '1'='1'; SELECT * FROM users WHERE 1=1--",
            "1 OR '1'='1'; SELECT * FROM users WHERE 1=1/*",
            "1' OR '1'='1'; SELECT * FROM users WHERE 1=1",
            "1' OR '1'='1'; SELECT * FROM users WHERE 1=1--",
            "1' OR '1'='1'; SELECT * FROM users WHERE 1=1/*",
            "1' OR '1'='1' UNION SELECT username, password FROM users",
            "1' OR '1'='1' UNION SELECT username, password FROM users--",
            "1' OR '1'='1' UNION SELECT username, password FROM users/*",
            "1' OR '1'='1' UNION SELECT username, password FROM users #",
            "1' OR '1'='1' UNION SELECT username, password FROM users /*",
            "1' OR '1'='1' UNION SELECT NULL, table_name FROM information_schema.tables",
            "1' OR '1'='1' UNION SELECT NULL, table_name FROM information_schema",
            "' OR '",
            "1' OR '1'='1' UNION SELECT NULL",
            "1' OR '1'='1' UNION SELECT NULL, table_name FROM information_schema.columns",
            "1' OR '1'='1' UNION SELECT NULL, table_name FROM",
            "' OR '1'='1' or",
        ];

        $testIdx = random_int(0, count($injections));

        $api = $this->getJson(route('api.get.db.supplier.supplier.read_any', [
            'company_id' => Hashids::encode($company->id),
            'search' => $injections[$testIdx],
            'paginate' => true,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();

        $api->assertJsonFragment([
            'total' => 0,
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

        $testIdx = random_int(0, count($injections));

        $api = $this->getJson(route('api.get.db.supplier.supplier.read_any', [
            'company_id' => Hashids::encode($company->id),
            'search' => $injections[$testIdx],
            'paginate' => false,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();

        $api->assertJsonFragment([
            'data' => [],
        ]);
    }

    public function test_supplier_api_call_read_any_with_or_without_pagination_expect_paginator_or_collection()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault()
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

        $api = $this->getJson(route('api.get.db.supplier.supplier.read_any', [
            'company_id' => Hashids::encode($company->id),
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

        $api = $this->getJson(route('api.get.db.supplier.supplier.read_any', [
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'paginate' => false,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
    }

    public function test_supplier_api_call_read_any_with_pagination_expect_several_per_page()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault()
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

        $api = $this->getJson(route('api.get.db.supplier.supplier.read_any', [
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

    public function test_supplier_api_call_read_any_with_search_expect_filtered_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault()
                ->has(ProductGroup::factory()->setCategoryToProduct()->count(5))
                ->has(Brand::factory()->count(5))
                ->has(Unit::factory()->setCategoryToProduct()->count(5))
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

        for ($i = 0; $i < 2; $i++) {
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
            )->insertStringInName('testing');

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

        $api = $this->getJson(route('api.get.db.supplier.supplier.read_any', [
            'company_id' => Hashids::encode($company->id),
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
            'total' => 1,
        ]);
    }

    public function test_supplier_api_call_read_any_with_special_char_in_search_expect_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault()
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

        $api = $this->getJson(route('api.get.db.supplier.supplier.read_any', [
            'company_id' => Hashids::encode($company->id),
            'search' => "!#$%&'()*+,-./:;<=>?@[\]^_`{|}~",
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

    public function test_supplier_api_call_read_any_with_negative_value_in_parameters_expect_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault()
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

        $api = $this->getJson(route('api.get.db.supplier.supplier.read_any', [
            'company_id' => Hashids::encode($company->id),
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

    public function test_supplier_api_call_read_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault()
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

        $api = $this->getJson(route('api.get.db.supplier.supplier.read', $supplier->ulid));

        $api->assertSuccessful();
    }

    public function test_supplier_api_call_read_without_ulid_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setIsDefault()
                    ->has(Supplier::factory())
            )->create();

        $this->actingAs($user);

        $this->getJson(route('api.get.db.supplier.supplier.read', null));
    }

    public function test_supplier_api_call_read_with_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setIsDefault()
                    ->has(Supplier::factory())
            )->create();

        $this->actingAs($user);

        $ulid = Str::ulid()->generate();

        $api = $this->getJson(route('api.get.db.supplier.supplier.read', $ulid));

        $api->assertStatus(404);
    }
}
