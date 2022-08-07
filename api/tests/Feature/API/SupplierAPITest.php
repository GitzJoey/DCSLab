<?php

namespace Tests\Feature\API;

use Exception;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use App\Models\Company;
use App\Models\Product;
use App\Models\Profile;
use App\Enums\UserRoles;
use App\Models\Supplier;
use App\Enums\UnitCategory;
use App\Enums\PaymentTermType;
use App\Enums\ProductCategory;
use App\Actions\RandomGenerator;
use App\Services\SupplierService;
use App\Enums\ProductGroupCategory;
use Vinkla\Hashids\Facades\Hashids;
use Database\Seeders\UnitTableSeeder;
use Database\Seeders\BrandTableSeeder;
use Database\Seeders\ProductTableSeeder;
use Database\Seeders\SupplierTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\ProductGroupTableSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;

class SupplierAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->supplierService = app(SupplierService::class);
        $this->randomGenerator = new RandomGenerator();
    }

    /* #region store */
    public function test_supplier_api_call_store_expect_successful()
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
        $productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::PRODUCTS->value]);

        $supplierProductsCount = $this->randomGenerator->generateNumber(1, $company->products()->count());
        $supplierProductIds = Product::where([
            ['company_id', '=', $companyId],
            ['brand_id', '!=', null]
        ])->take($supplierProductsCount)->pluck('id');
        
        $productIds = [];
        foreach ($supplierProductIds as $supplierProductId) {           
            array_push($productIds, Hashids::encode($supplierProductId));
        }

        $mainProducts = [];
        foreach ($supplierProductIds as $supplierProductId) {
            $mainProductId = $this->randomGenerator->generateNumber(0, 1);
            if ($mainProductId == 1) {
                array_push($mainProducts, Hashids::encode($supplierProductId));
            }
        }

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($companyId)
        ])->toArray();
        $supplierArr['payment_term_type'] = $this->faker->randomElement(PaymentTermType::toArrayEnum())->name;
        $supplierArr['pic_name'] = $this->faker->name();
        $supplierArr['email'] = $this->faker->email();
        $supplierArr['productIds'] = $productIds;
        $supplierArr['mainProducts'] = $mainProducts;

        $api = $this->json('POST', route('api.post.db.supplier.supplier.save'), $supplierArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('suppliers', [
            'company_id' => Hashids::decode($supplierArr['company_id'])[0],
            'code' => $supplierArr['code'],
            'name' => $supplierArr['name'],
            'payment_term_type' => PaymentTermType::fromName($supplierArr['payment_term_type']),
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

    public function test_supplier_api_call_store_with_existing_code_in_same_company_expect_failed()
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
        $productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::PRODUCTS->value]);

        $supplierSeeder = new SupplierTableSeeder();
        $supplierSeeder->callWith(SupplierTableSeeder::class, [3, $companyId]);

        $supplierProductsCount = $this->randomGenerator->generateNumber(1, $company->products()->count());
        $supplierProductIds = Product::where([
            ['company_id', '=', $companyId],
            ['brand_id', '!=', null]
        ])->take($supplierProductsCount)->pluck('id');
        
        $productIds = [];
        foreach ($supplierProductIds as $supplierProductId) {           
            array_push($productIds, Hashids::encode($supplierProductId));
        }

        $mainProducts = [];
        foreach ($supplierProductIds as $supplierProductId) {
            $mainProductId = $this->randomGenerator->generateNumber(0, 1);
            if ($mainProductId == 1) {
                array_push($mainProducts, Hashids::encode($supplierProductId));
            }
        }
        
        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($companyId),
            'code' => $company->suppliers()->first()->code,
        ])->toArray();
        $supplierArr['payment_term_type'] = $this->faker->randomElement(PaymentTermType::toArrayEnum())->name;
        $supplierArr['pic_name'] = $this->faker->name();
        $supplierArr['email'] = $this->faker->email();
        $supplierArr['productIds'] = $productIds;
        $supplierArr['mainProducts'] = $mainProducts;

        $api = $this->json('POST', route('api.post.db.supplier.supplier.save'), $supplierArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_supplier_api_call_store_with_empty_string_parameters_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                ->has(Company::factory()->setIsDefault(), 'companies')
                ->create();

        $this->actingAs($user);

        $supplierArr = [];
        [];
        [];
        $api = $this->json('POST', route('api.post.db.supplier.supplier.save'), $supplierArr);

        $api->assertStatus(422);
    }
    /* #endregion */

    /* #region list */
    public function test_supplier_api_call_list_with_or_without_pagination_expect_paginator_or_collection()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

                $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

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

        $supplierProductsCount = $this->randomGenerator->generateNumber(1, $company->products()->count());
        $supplierProductIds = Product::where([
            ['company_id', '=', $companyId],
            ['brand_id', '!=', null]
        ])->take($supplierProductsCount)->pluck('id');
        
        $productIds = [];
        foreach ($supplierProductIds as $supplierProductId) {           
            array_push($productIds, Hashids::encode($supplierProductId));
        }

        $mainProducts = [];
        foreach ($supplierProductIds as $supplierProductId) {
            $mainProductId = $this->randomGenerator->generateNumber(0, 1);
            if ($mainProductId == 1) {
                array_push($mainProducts, Hashids::encode($supplierProductId));
            }
        }

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($companyId)
        ])->toArray();
        $supplierArr['payment_term_type'] = $this->faker->randomElement(PaymentTermType::toArrayEnum())->name;
        $supplierArr['pic_name'] = $this->faker->name();
        $supplierArr['email'] = $this->faker->email();
        $supplierArr['productIds'] = $productIds;
        $supplierArr['mainProducts'] = $mainProducts;

        $api = $this->getJson(route('api.get.db.supplier.supplier.list', [
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

        $api = $this->getJson(route('api.get.db.supplier.supplier.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => '',
            'paginate' => false,
            'page' => 1,
            'perPage' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
    }

    public function test_supplier_api_call_list_with_search_expect_filtered_results()
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
        $productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::PRODUCTS->value]);
        
        for ($i = 0; $i < 3; $i++) {
            $supplierArr = Supplier::factory()->insertStringInName('testing')->make([
                'company_id' => $user->companies->first()->id
            ])->toArray();
            
            $picArr = Profile::factory()->make()->toArray();
            $picArr['name'] = strtolower($picArr['first_name'] . $picArr['last_name']) . $this->randomGenerator->generateNumber(1, 999);
            $picArr['email'] = $picArr['name'] . '@something.com';
            $picArr['contact'] = $supplierArr['contact'];
            $picArr['address'] = $supplierArr['address'];
            $picArr['city'] = $supplierArr['city'];
            $picArr['tax_id'] = $supplierArr['tax_id'];

            $supplierProductsCount = $this->randomGenerator->generateNumber(1, $company->products()->count());
            $productIds = Product::where([
                ['company_id', '=', $companyId],
                ['brand_id', '!=', null]
            ])->take($supplierProductsCount)->pluck('id');
            
            $productsArr = [];
            foreach ($productIds as $productId) {
                $supplierProduct = [];
                $supplierProduct['product_id'] = $productId;
                $supplierProduct['main_product'] = $this->randomGenerator->generateNumber(0, 1);

                array_push($productsArr, $supplierProduct);
            }

            $this->supplierService->create(
                supplierArr: $supplierArr,
                picArr: $picArr,
                productsArr: $productsArr
            );
        }

        $supplierSeeder = new SupplierTableSeeder();
        $supplierSeeder->callWith(SupplierTableSeeder::class, [5, $companyId]);

        $api = $this->getJson(route('api.get.db.supplier.supplier.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => 'testing',
            'paginate' => true,
            'page' => 1,
            'perPage' => 3,
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
            'total' => 3,
        ]);
    }

    public function test_supplier_api_call_list_without_search_querystring_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);

        $productSeeder = new ProductTableSeeder();
        $productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::PRODUCTS->value]);

        $supplierProductsCount = $this->randomGenerator->generateNumber(1, $company->products()->count());
        $supplierProductIds = Product::where([
            ['company_id', '=', $companyId],
            ['brand_id', '!=', null]
        ])->take($supplierProductsCount)->pluck('id');
        
        $productIds = [];
        foreach ($supplierProductIds as $supplierProductId) {           
            array_push($productIds, Hashids::encode($supplierProductId));
        }

        $mainProducts = [];
        foreach ($supplierProductIds as $supplierProductId) {
            $mainProductId = $this->randomGenerator->generateNumber(0, 1);
            if ($mainProductId == 1) {
                array_push($mainProducts, Hashids::encode($supplierProductId));
            }
        }

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($companyId)
        ])->toArray();
        $supplierArr['payment_term_type'] = $this->faker->randomElement(PaymentTermType::toArrayEnum())->name;
        $supplierArr['pic_name'] = $this->faker->name();
        $supplierArr['email'] = $this->faker->email();
        $supplierArr['productIds'] = $productIds;
        $supplierArr['mainProducts'] = $mainProducts;

        $api = $this->getJson(route('api.get.db.supplier.supplier.list', [
            'companyId' => Hashids::encode($companyId),
        ]));

        $api->assertStatus(422);
    }

    public function test_supplier_api_call_list_with_special_char_in_search_expect_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

                $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);

        $productSeeder = new ProductTableSeeder();
        $productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::PRODUCTS->value]);

        $supplierProductsCount = $this->randomGenerator->generateNumber(1, $company->products()->count());
        $supplierProductIds = Product::where([
            ['company_id', '=', $companyId],
            ['brand_id', '!=', null]
        ])->take($supplierProductsCount)->pluck('id');
        
        $productIds = [];
        foreach ($supplierProductIds as $supplierProductId) {           
            array_push($productIds, Hashids::encode($supplierProductId));
        }

        $mainProducts = [];
        foreach ($supplierProductIds as $supplierProductId) {
            $mainProductId = $this->randomGenerator->generateNumber(0, 1);
            if ($mainProductId == 1) {
                array_push($mainProducts, Hashids::encode($supplierProductId));
            }
        }

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($companyId)
        ])->toArray();
        $supplierArr['payment_term_type'] = $this->faker->randomElement(PaymentTermType::toArrayEnum())->name;
        $supplierArr['pic_name'] = $this->faker->name();
        $supplierArr['email'] = $this->faker->email();
        $supplierArr['productIds'] = $productIds;
        $supplierArr['mainProducts'] = $mainProducts;


        $api = $this->getJson(route('api.get.db.supplier.supplier.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => "!#$%&'()*+,-./:;<=>?@[\]^_`{|}~",
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

    public function test_supplier_api_call_list_with_negative_value_in_parameters_expect_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

                $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);

        $productSeeder = new ProductTableSeeder();
        $productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::PRODUCTS->value]);

        $supplierProductsCount = $this->randomGenerator->generateNumber(1, $company->products()->count());
        $supplierProductIds = Product::where([
            ['company_id', '=', $companyId],
            ['brand_id', '!=', null]
        ])->take($supplierProductsCount)->pluck('id');
        
        $productIds = [];
        foreach ($supplierProductIds as $supplierProductId) {           
            array_push($productIds, Hashids::encode($supplierProductId));
        }

        $mainProducts = [];
        foreach ($supplierProductIds as $supplierProductId) {
            $mainProductId = $this->randomGenerator->generateNumber(0, 1);
            if ($mainProductId == 1) {
                array_push($mainProducts, Hashids::encode($supplierProductId));
            }
        }

        $supplierArr = Supplier::factory()->make([
            'company_id' => Hashids::encode($companyId)
        ])->toArray();
        $supplierArr['payment_term_type'] = $this->faker->randomElement(PaymentTermType::toArrayEnum())->name;
        $supplierArr['pic_name'] = $this->faker->name();
        $supplierArr['email'] = $this->faker->email();
        $supplierArr['productIds'] = $productIds;
        $supplierArr['mainProducts'] = $mainProducts;
        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.supplier.supplier.list', [
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
    public function test_supplier_api_call_read_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);

        $productSeeder = new ProductTableSeeder();
        $productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::PRODUCTS->value]);

        $supplierSeeder = new SupplierTableSeeder();
        $supplierSeeder->callWith(SupplierTableSeeder::class, [1, $companyId]);

        $supplierProductsCount = $this->randomGenerator->generateNumber(1, $company->products()->count());
        $supplierProductIds = Product::where([
            ['company_id', '=', $companyId],
            ['brand_id', '!=', null]
        ])->take($supplierProductsCount)->pluck('id');
        
        $productIds = [];
        foreach ($supplierProductIds as $supplierProductId) {           
            array_push($productIds, Hashids::encode($supplierProductId));
        }

        $mainProducts = [];
        foreach ($supplierProductIds as $supplierProductId) {
            $mainProductId = $this->randomGenerator->generateNumber(0, 1);
            if ($mainProductId == 1) {
                array_push($mainProducts, Hashids::encode($supplierProductId));
            }           
        }

        $uuid = $company->suppliers()->inRandomOrder()->first()->uuid;

        $api = $this->getJson(route('api.get.db.supplier.supplier.read', $uuid));

        $api->assertSuccessful();
    }

    public function test_supplier_api_call_read_without_uuid_expect_exception()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $this->getJson(route('api.get.db.supplier.supplier.read', null));
    }

    public function test_supplier_api_call_read_with_nonexistance_uuid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

                $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $uuid = $this->faker->uuid();

        $api = $this->getJson(route('api.get.db.supplier.supplier.read', $uuid));

        $api->assertStatus(404);
    }
    /* #endregion */

    /* #region update */
    public function test_supplier_api_call_update_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);

        $productSeeder = new ProductTableSeeder();
        $productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::PRODUCTS->value]);

        $supplierSeeder = new SupplierTableSeeder();
        $supplierSeeder->callWith(SupplierTableSeeder::class, [1, $companyId]);

        $supplierProductsCount = $this->randomGenerator->generateNumber(1, $company->products()->count());
        $supplierProductIds = Product::where([
            ['company_id', '=', $companyId],
            ['brand_id', '!=', null]
        ])->take($supplierProductsCount)->pluck('id');
        
        $productIds = [];
        foreach ($supplierProductIds as $supplierProductId) {           
            array_push($productIds, Hashids::encode($supplierProductId));
        }

        $mainProducts = [];
        foreach ($supplierProductIds as $supplierProductId) {
            $mainProductId = $this->randomGenerator->generateNumber(0, 1);
            if ($mainProductId == 1) {
                array_push($mainProducts, Hashids::encode($supplierProductId));
            }           
        }

        $supplier = $company->suppliers()->inRandomOrder()->first();
        $supplierArr = array_merge([
            'company_id' => Hashids::encode($companyId),
        ], Supplier::factory()->make()->toArray());
        $supplierArr['payment_term_type'] = $this->faker->randomElement(PaymentTermType::toArrayEnum())->name;
        $supplierArr['email'] = $supplier->user()->first()->email;
        $supplierArr['productIds'] = $productIds;
        $supplierArr['mainProducts'] = $mainProducts;
        
        $api = $this->json('POST', route('api.post.db.supplier.supplier.edit', $supplier->uuid), $supplierArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'company_id' => $companyId,
            'code' => $supplierArr['code'],
            'name' => $supplierArr['name'],
            'payment_term_type' => PaymentTermType::fromName($supplierArr['payment_term_type']),
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

    public function test_supplier_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $supplierSeeder = new SupplierTableSeeder();
        $supplierSeeder->callWith(SupplierTableSeeder::class, [2, $companyId]);

        $suppliers = $company->suppliers()->inRandomOrder()->take(2)->get();
        $supplier_1 = $suppliers[0];
        $supplier_2 = $suppliers[1];

        $supplierArr = array_merge([
            'company_id' => Hashids::encode($companyId),
        ], Supplier::factory()->make([
            'code' => $supplier_1->code,
        ])->toArray());

        $api = $this->json('POST', route('api.post.db.supplier.supplier.edit', $supplier_2->uuid), $supplierArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_supplier_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->count(5)->state(new Sequence(['default' => true], ['default' => false])), 'companies')
                    ->create();

        $this->actingAs($user);

        $supplierSeeder = new SupplierTableSeeder();

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;
        $supplierSeeder->callWith(SupplierTableSeeder::class, [3, $companyId_1]);

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;
        $supplierSeeder->callWith(SupplierTableSeeder::class, [3, $companyId_2]);

        $supplier_company_2 = $company_2->suppliers()->inRandomOrder()->first();
        $supplierArr = array_merge([
            'company_id' => Hashids::encode($companyId_2),
            'email' => $supplier_company_2->user()->first()->email,
        ], Supplier::factory()->make([
            'code' => $company_1->suppliers()->inRandomOrder()->first()->code,
        ])->toArray());

        $api = $this->json('POST', route('api.post.db.supplier.supplier.edit', $supplier_company_2->uuid), $supplierArr);

        $api->assertSuccessful();
    }
    /* #endregion */

    /* #region delete */
    public function test_supplier_api_call_delete_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $productGroupSeeder = new ProductGroupTableSeeder();
        $productGroupSeeder->callWith(ProductGroupTableSeeder::class, [3, $companyId, ProductGroupCategory::PRODUCTS->value]);

        $brandSeeder = new BrandTableSeeder();
        $brandSeeder->callWith(BrandTableSeeder::class, [3, $companyId]);

        $unitSeeder = new UnitTableSeeder();
        $unitSeeder->callWith(UnitTableSeeder::class, [3, $companyId, UnitCategory::PRODUCTS->value]);

        $productSeeder = new ProductTableSeeder();
        $productSeeder->callWith(ProductTableSeeder::class, [10, $companyId, ProductCategory::PRODUCTS->value]);

        $supplierSeeder = new SupplierTableSeeder();
        $supplierSeeder->callWith(SupplierTableSeeder::class, [1, $companyId]);

        $supplierProductsCount = $this->randomGenerator->generateNumber(1, $company->products()->count());
        $supplierProductIds = Product::where([
            ['company_id', '=', $companyId],
            ['brand_id', '!=', null]
        ])->take($supplierProductsCount)->pluck('id');
        
        $productIds = [];
        foreach ($supplierProductIds as $supplierProductId) {           
            array_push($productIds, Hashids::encode($supplierProductId));
        }

        $mainProducts = [];
        foreach ($supplierProductIds as $supplierProductId) {
            $mainProductId = $this->randomGenerator->generateNumber(0, 1);
            if ($mainProductId == 1) {
                array_push($mainProducts, Hashids::encode($supplierProductId));
            }           
        }

        $supplier = $company->suppliers()->inRandomOrder()->first();

        $api = $this->json('POST', route('api.post.db.supplier.supplier.delete', $supplier->uuid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('suppliers', [
            'id' => $supplier->id,
        ]);
    }

    public function test_supplier_api_call_delete_of_nonexistance_uuid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $uuid = $this->faker->uuid();

        $api = $this->json('POST', route('api.post.db.supplier.supplier.delete', $uuid));

        $api->assertStatus(404);
    }

    public function test_supplier_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.db.supplier.supplier.delete', null));
    }
    /* #endregion */

    /* #region others */

    /* #endregion */
}
