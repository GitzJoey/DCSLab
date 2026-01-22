<?php

namespace Tests\Feature\API\ProductCategoryAPI;

use App\Enums\ProductCategoryTypeEnum;
use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\ProductCategory;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class ProductCategoryAPIReadTest extends APITestCase
{
    public function test_product_category_api_call_read_with_sql_injection_expect_injection_ignored()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        ProductCategory::factory()->for($company)->create();

        $injections = [
            "' OR '1'='1",
            '1 UNION SELECT username, password FROM users',
            '1; DROP TABLE users',
            "' OR '1'='1' --",
            '1 OR SLEEP(5)',
            "1; INSERT INTO logs (message) VALUES ('Injected SQL query')",
            "1; UPDATE users SET password = 'hacked' WHERE id = 1; --",
            "admin'--",
            "' OR 1=1 --",
        ];

        $testIdx = random_int(0, count($injections) - 1);

        $api = $this->getJson(route('api.get.product_category.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => $injections[$testIdx],
            'type' => null,
            'refresh' => true,
            'paginate' => [
                'page' => 1,
                'per_page' => 10,
            ],
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

        $testIdx = random_int(0, count($injections) - 1);

        $api = $this->getJson(route('api.get.product_category.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => $injections[$testIdx],
            'type' => null,
            'refresh' => true,
            'get' => [
                'limit' => 10,
            ],
        ]));

        $api->assertSuccessful();

        $api->assertJsonFragment([
            'data' => [],
        ]);
    }

    public function test_product_category_api_call_read_any_with_or_without_pagination_expect_paginator_or_collection()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        ProductCategory::factory()->for($company)->create();

        $api = $this->getJson(route('api.get.product_category.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'type' => null,
            'refresh' => true,
            'paginate' => [
                'page' => 1,
                'per_page' => 10,
            ],
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

        $api = $this->getJson(route('api.get.product_category.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'type' => null,
            'refresh' => true,
            'get' => [
                'limit' => 10,
            ],
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
        ]);
    }

    public function test_product_category_api_call_read_any_with_pagination_expect_several_per_page()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        ProductCategory::factory()->for($company)->count(30)->create();

        $api = $this->getJson(route('api.get.product_category.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'type' => null,
            'refresh' => true,
            'paginate' => [
                'page' => 1,
                'per_page' => 25,
            ],
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

    public function test_product_category_api_call_read_any_with_search_expect_filtered_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        ProductCategory::factory()->for($company)->count(2)->create();

        ProductCategory::factory()->for($company)
            ->create([
                'name' => 'testing',
            ]);

        ProductCategory::factory()->for($company)
            ->create([
                'code' => 'testing_code',
            ]);

        $api = $this->getJson(route('api.get.product_category.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => 'testing',
            'type' => null,
            'refresh' => true,
            'paginate' => [
                'page' => 1,
                'per_page' => 25,
            ],
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
            'total' => 2,
        ]);
    }

    public function test_product_category_api_call_read_any_without_required_parameters_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        ProductCategory::factory()->for($company)->create();

        $api = $this->getJson(route('api.get.product_category.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
        ]));

        $api->assertUnprocessable();
    }

    public function test_product_category_api_call_read_any_with_special_char_in_search_expect_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        ProductCategory::factory()->for($company)->create();

        $api = $this->getJson(route('api.get.product_category.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => " !#$%&'()*+,-./:;<=>?@[\\]^_`{|}~",
            'type' => null,
            'refresh' => false,
            'paginate' => [
                'page' => 1,
                'per_page' => 25,
            ],
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

    public function test_product_category_api_call_read_any_with_type_filter_expect_filtered_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        ProductCategory::factory()->for($company)
            ->create([
                'type' => ProductCategoryTypeEnum::PRODUCT,
            ]);

        ProductCategory::factory()->for($company)
            ->create([
                'type' => ProductCategoryTypeEnum::SERVICE,
            ]);

        $api = $this->getJson(route('api.get.product_category.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'type' => ProductCategoryTypeEnum::PRODUCT->value,
            'refresh' => true,
            'paginate' => [
                'page' => 1,
                'per_page' => 25,
            ],
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

    public function test_product_category_api_call_read_single_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productCategory = ProductCategory::factory()->for($company)->create();

        $api = $this->getJson(route('api.get.product_category.read', $productCategory->ulid));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
        ]);
    }
}
