<?php

namespace Tests\Feature\API\ProductGroupAPI;

use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\ProductGroup;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Str;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class ProductGroupAPIReadTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_product_group_api_call_read_any_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productGroupFactory = ProductGroup::factory()->make();
        $api = $this->getJson(route('api.get.db.product.product_group.read_any', [
            'company_id' => Hashids::encode($company->id),
            'category' => $productGroupFactory->category->value,
            'search' => '',
            'paginate' => true,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertStatus(401);
    }

    public function test_product_group_api_call_read_any_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory())
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productGroupFactory = ProductGroup::factory()->make();
        $api = $this->getJson(route('api.get.db.product.product_group.read_any', [
            'company_id' => Hashids::encode($company->id),
            'category' => $productGroupFactory->category->value,
            'search' => '',
            'paginate' => true,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertStatus(403);
    }

    public function test_product_group_api_call_read_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $ulid = $company->productGroups()->inRandomOrder()->first()->ulid;

        $api = $this->getJson(route('api.get.db.product.product_group.read', $ulid));

        $api->assertStatus(401);
    }

    public function test_product_group_api_call_read_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory())
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $ulid = $company->productGroups()->inRandomOrder()->first()->ulid;

        $api = $this->getJson(route('api.get.db.product.product_group.read', $ulid));

        $api->assertStatus(403);
    }

    public function test_product_group_api_call_read_any_with_or_without_pagination_expect_paginator_or_collection()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory())
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productGroupFactory = ProductGroup::factory()->make();
        $api = $this->getJson(route('api.get.db.product.product_group.read_any', [
            'company_id' => Hashids::encode($company->id),
            'category' => $productGroupFactory->category->value,
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

        $api = $this->getJson(route('api.get.db.product.product_group.read_any', [
            'company_id' => Hashids::encode($company->id),
            'category' => $productGroupFactory->category->value,
            'search' => '',
            'paginate' => false,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
    }

    public function test_product_group_api_call_read_any_with_pagination_expect_several_per_page()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory())
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $productGroupFactory = ProductGroup::factory()->make();
        $api = $this->getJson(route('api.get.db.product.product_group.read_any', [
            'company_id' => Hashids::encode($company->id),
            'category' => $productGroupFactory->category->value,
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

    public function test_product_group_api_call_read_any_with_search_expect_filtered_results()
    {
        $productGroupCount = 5;
        $idxTest = random_int(0, $productGroupCount - 1);
        $defaultName = ProductGroup::factory()->make()->name;
        $testName = ProductGroup::factory()->insertStringInName('testing')->make()->name;

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory()->count($productGroupCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'name' => $sequence->index == $idxTest ? $testName : $defaultName,
                        ]
                    ))
                ))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.db.product.product_group.read_any', [
            'company_id' => Hashids::encode($company->id),
            'category' => null,
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

    public function test_product_group_api_call_read_any_without_search_querystring_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault()
                ->has(ProductGroup::factory()->count(2))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.db.product.product_group.read_any', [
            'company_id' => Hashids::encode($company->id),
            'category' => null,
        ]));

        $api->assertStatus(422);
    }

    public function test_product_group_api_call_read_with_sql_injection_expect_injection_ignored()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_product_group_api_call_read_any_with_special_char_in_search_expect_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory())
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.db.product.product_group.read_any', [
            'company_id' => Hashids::encode($company->id),
            'category' => null,
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

    public function test_product_group_api_call_read_any_with_negative_value_in_parameters_expect_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory())
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.db.product.product_group.read_any', [
            'company_id' => Hashids::encode($company->id),
            'category' => null,
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

    public function test_product_group_api_call_read_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory())
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $ulid = $company->productGroups()->inRandomOrder()->first()->ulid;

        $api = $this->getJson(route('api.get.db.product.product_group.read', $ulid));

        $api->assertSuccessful();
    }

    public function test_product_group_api_call_read_without_ulid_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $this->getJson(route('api.get.db.product.product_group.read', null));
    }

    public function test_product_group_api_call_read_with_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory())
            )->create();

        $this->actingAs($user);

        $ulid = Str::ulid()->generate();

        $api = $this->getJson(route('api.get.db.product.product_group.read', $ulid));

        $api->assertStatus(404);
    }
}
