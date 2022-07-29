<?php

namespace Tests\Feature\API;

use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use App\Models\Company;
use App\Models\Product;
use App\Enums\UserRoles;
use App\Enums\UnitCategory;
use App\Enums\ProductGroupCategory;
use Vinkla\Hashids\Facades\Hashids;
use Database\Seeders\UnitTableSeeder;
use Database\Seeders\BrandTableSeeder;
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
    public function test_product_api_call_store_expect_successful()
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

        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId),
        ], Product::factory()->make()->toArray());

        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId),
            'product_group_id' => $company->productGroups()->inRandomOrder()->first()->id,
            'brand_id' => $company->brands()->inRandomOrder()->first()->id,
            'unit_id' => $company->units()->inRandomOrder()->first()->id
        ], Product::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('products', [
            'company_id' => $companyId,
            'code' => $productArr['code'],
            'name' => $productArr['name'],
        ]);
    }

    public function test_product_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        Product::factory()->create([
            'company_id' => $companyId,
            'code' => 'test1',
        ]);

        $this->actingAs($user);

        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId),
        ], Product::factory()->make([
            'code' => 'test1',
        ])->toArray());

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

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;

        Product::factory()->create([
            'company_id' => $companyId_1,
            'code' => 'test1',
        ]);

        $this->actingAs($user);

        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId_2),
        ], Product::factory()->make([
            'code' => 'test1',
        ])->toArray());

        $api = $this->json('POST', route('api.post.db.product.product.save'), $productArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('products', [
            'company_id' => $companyId_2,
            'code' => $productArr['code'],
        ]);
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
                    ->has(Company::factory()->setIsDefault()
                            ->has(Product::factory()->count(15), 'products'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

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

        $api = $this->getJson(route('api.get.db.product.product.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => '',
            'paginate' => false,
            'page' => 1,
            'perPage' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
    }

    public function test_product_api_call_list_with_search_expect_filtered_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        Product::factory()->count(10)->create([
            'company_id' => $companyId,
            'name' => 'Kantor Cabang '.$this->faker->randomElement(['Utama', 'Pembantu', 'Daerah']).' '.'testing',
        ]);

        Product::factory()->count(10)->create([
            'company_id' => $companyId,
        ]);

        $this->actingAs($user);

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
            'total' => 10,
        ]);
    }

    public function test_product_api_call_list_without_search_querystring_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Product::factory()->count(2), 'products'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

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
                    ->has(Company::factory()->setIsDefault()
                            ->has(Product::factory()->count(5), 'products'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

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
                    ->has(Company::factory()->setIsDefault()
                            ->has(Product::factory()->count(5), 'products'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

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
                    ->has(Company::factory()->setIsDefault()
                            ->has(Product::factory()->count(5), 'products'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $uuid = $company->products()->inRandomOrder()->first()->uuid;

        $api = $this->getJson(route('api.get.db.product.product.read', $uuid));

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

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $this->getJson(route('api.get.db.product.product.read', null));
    }

    public function test_product_api_call_read_with_nonexistance_uuid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Product::factory()->count(5), 'products'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $uuid = $this->faker->uuid();

        $api = $this->getJson(route('api.get.db.product.product.read', $uuid));

        $api->assertStatus(404);
    }
    /* #endregion */

    /* #region update */
    public function test_product_api_call_update_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Product::factory()->count(5), 'products'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $product = $company->products()->inRandomOrder()->first();
        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId),
        ], Product::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product->uuid), $productArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'company_id' => $companyId,
            'code' => $productArr['code'],
            'name' => $productArr['name'],
        ]);
    }

    public function test_product_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Product::factory()->count(5), 'products'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $products = $company->products()->inRandomOrder()->take(2)->get();
        $product_1 = $products[0];
        $product_2 = $products[1];

        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId),
        ], Product::factory()->make([
            'code' => $product_1->code,
        ])->toArray());

        $api = $this->json('POST', route('api.post.db.product.product.edit', $product_2->uuid), $productArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->count(2)->state(new Sequence(['default' => true], ['default' => false])), 'companies')
                    ->create();

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;

        Product::factory()->create([
            'company_id' => $companyId_1,
            'code' => 'test1',
        ]);

        Product::factory()->create([
            'company_id' => $companyId_2,
            'code' => 'test2',
        ]);

        $this->actingAs($user);

        $productArr = array_merge([
            'company_id' => Hashids::encode($companyId_2),
        ], Product::factory()->make([
            'code' => 'test1',
        ])->toArray());

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
                    ->has(Company::factory()->setIsDefault()
                            ->has(Product::factory()->count(5), 'products'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $product = $company->products()->inRandomOrder()->first();

        $this->actingAs($user);

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

    /* #endregion */
}
