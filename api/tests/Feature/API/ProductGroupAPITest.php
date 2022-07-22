<?php

namespace Tests\Feature\API;

use Exception;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use App\Models\Company;
use App\Enums\UserRoles;
use App\Models\ProductGroup;
use App\Enums\ProductGroupCategory;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\Factories\Sequence;

class ProductGroupAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #region store

    public function test_product_group_api_call_store_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        $this->actingAs($user);

        $productGroupArr = ProductGroup::factory()->make([
            'company_id' => Hashids::encode($companyId),
        ])->toArray();
        $productGroupArr['category'] = $this->faker->randomElement(ProductGroupCategory::toArrayEnum())->name;

        $api = $this->json('POST', route('api.post.db.product.product_group.save'), $productGroupArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('product_groups', [
            'company_id' => Hashids::decode($productGroupArr['company_id'])[0],
            'code' => $productGroupArr['code'],
            'name' => $productGroupArr['name'],
            'category' => ProductGroupCategory::fromName($productGroupArr['category']),
        ]);
    }

    public function test_product_group_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        ProductGroup::factory()->create([
            'company_id' => $companyId,
            'code' => 'test1',
        ]);

        $this->actingAs($user);

        $productGroupArr = ProductGroup::factory()->make([
            'company_id' => Hashids::encode($companyId),
            'code' => 'test1',
        ])->toArray();
        $productGroupArr['category'] = $this->faker->randomElement(ProductGroupCategory::toArrayEnum())->name;

        $api = $this->json('POST', route('api.post.db.product.product_group.save'), $productGroupArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_group_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                ->has(Company::factory()->setIsDefault(), 'companies')
                ->create();

        $this->actingAs($user);

        $productGroupArr = [];
        $api = $this->json('POST', route('api.post.db.product.product_group.save'), $productGroupArr);

        $api->assertJsonValidationErrors(['company_id', 'code', 'name']);
    }

    #endregion

    #region list

    public function test_product_group_api_call_list_with_or_without_pagination_expect_paginator_or_collection()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(ProductGroup::factory()->count(15), 'productGroups'), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.product.product_group.list', [
            'companyId' => Hashids::encode($companyId),
            'category' => $this->faker->randomElement(ProductGroupCategory::toArrayEnum())->name,
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

        $api = $this->getJson(route('api.get.db.product.product_group.list', [
            'companyId' => Hashids::encode($companyId),
            'category' => $this->faker->randomElement(ProductGroupCategory::toArrayEnum())->name,
            'search' => '',
            'paginate' => false,
            'page' => 1,
            'perPage' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
    }

    public function test_product_group_api_call_list_with_search_expect_filtered_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        ProductGroup::factory()->insertStringInName('testing')->count(10)->create([
            'company_id' => $companyId,
            'category' => 3,
        ]);

        ProductGroup::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => 3,
        ]);

        $this->actingAs($user);

        $category = ProductGroupCategory::PRODUCTS_AND_SERVICES->name;

        $api = $this->getJson(route('api.get.db.product.product_group.list', [
            'companyId' => Hashids::encode($companyId),
            'category' => $category,
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

    public function test_product_group_api_call_list_without_search_querystring_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(ProductGroup::factory()->count(2), 'productGroups'), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        $this->actingAs($user);

        $category = ProductGroupCategory::PRODUCTS_AND_SERVICES->name;

        $api = $this->getJson(route('api.get.db.product.product_group.list', [
            'companyId' => Hashids::encode($companyId),
            'category' => $category,
        ]));

        $api->assertStatus(422);
    }

    public function test_product_group_api_call_list_with_special_char_in_search_expect_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(ProductGroup::factory()->count(5), 'productGroups'), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        $this->actingAs($user);

        $category = ProductGroupCategory::PRODUCTS_AND_SERVICES->name;

        $api = $this->getJson(route('api.get.db.product.product_group.list', [
            'companyId' => Hashids::encode($companyId),
            'category' => $category,
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

    public function test_product_group_api_call_list_with_negative_value_in_parameters_expect_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(ProductGroup::factory()->count(5), 'productGroups'), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        $this->actingAs($user);

        $category = ProductGroupCategory::PRODUCTS_AND_SERVICES->name;

        $api = $this->getJson(route('api.get.db.product.product_group.list', [
            'companyId' => Hashids::encode($companyId),
            'category' => $category,
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

    #endregion

    #region read

    public function test_product_group_api_call_read_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(ProductGroup::factory()->count(5), 'productGroups'), 'companies')
                    ->create();

        $company = $user->companies->first();

        $this->actingAs($user);

        $uuid = $company->productGroups()->inRandomOrder()->first()->uuid;

        $api = $this->getJson(route('api.get.db.product.product_group.read', $uuid));

        $api->assertSuccessful();
    }

    public function test_product_group_api_call_read_without_uuid_expect_exception()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $this->getJson(route('api.get.db.product.product_group.read', null));
    }

    public function test_product_group_api_call_read_with_nonexistance_uuid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(ProductGroup::factory()->count(5), 'productGroups'), 'companies')
                    ->create();

        $this->actingAs($user);

        $uuid = $this->faker->uuid();

        $api = $this->getJson(route('api.get.db.product.product_group.read', $uuid));

        $api->assertStatus(404);
    }

    #endregion

    #region update

    public function test_product_group_api_call_update_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(ProductGroup::factory()->count(5), 'productGroups'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $productGroup = $company->productGroups()->inRandomOrder()->first();

        $productGroupArr = ProductGroup::factory()->make([
            'company_id' => Hashids::encode($companyId),
        ])->toArray();
        $productGroupArr['category'] = $this->faker->randomElement(ProductGroupCategory::toArrayEnum())->name;

        $api = $this->json('POST', route('api.post.db.product.product_group.edit', $productGroup->uuid), $productGroupArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('product_groups', [
            'id' => $productGroup->id,
            'company_id' => $companyId,
            'code' => $productGroupArr['code'],
            'name' => $productGroupArr['name'],
            'category' => ProductGroupCategory::fromName($productGroupArr['category']),
        ]);
    }

    public function test_product_group_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(ProductGroup::factory()->count(5), 'productGroups'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $productGroups = $company->productGroups()->inRandomOrder()->take(2)->get();
        $productGroup_1 = $productGroups[0];
        $productGroup_2 = $productGroups[1];

        $productGroupArr = ProductGroup::factory()->make([
            'company_id' => Hashids::encode($companyId),
            'code' => $productGroup_1->code,
        ])->toArray();
        $productGroupArr['category'] = $this->faker->randomElement(ProductGroupCategory::toArrayEnum())->name;

        $api = $this->json('POST', route('api.post.db.product.product_group.edit', $productGroup_2->uuid), $productGroupArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_product_group_api_call_update_and_use_existing_code_in_different_company_expect_successful()
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

        ProductGroup::factory()->create([
            'company_id' => $companyId_1,
            'code' => 'test1',
        ]);

        ProductGroup::factory()->create([
            'company_id' => $companyId_2,
            'code' => 'test2',
        ]);

        $this->actingAs($user);

        $productGroupArr = ProductGroup::factory()->make([
            'company_id' => Hashids::encode($companyId_2),
            'code' => 'test1',
        ])->toArray();
        $productGroupArr['category'] = $this->faker->randomElement(ProductGroupCategory::toArrayEnum())->name;

        $api = $this->json('POST', route('api.post.db.product.product_group.edit', $company_2->productGroups()->first()->uuid), $productGroupArr);

        $api->assertSuccessful();
    }

    #endregion

    #region delete

    public function test_product_group_api_call_delete_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(ProductGroup::factory()->count(5), 'productGroups'), 'companies')
                    ->create();

        $company = $user->companies->first();

        $productGroup = $company->productGroups()->inRandomOrder()->first();

        $this->actingAs($user);

        $api = $this->json('POST', route('api.post.db.product.product_group.delete', $productGroup->uuid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('product_groups', [
            'id' => $productGroup->id,
        ]);
    }

    public function test_product_group_api_call_delete_of_nonexistance_uuid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $uuid = $this->faker->uuid();

        $api = $this->json('POST', route('api.post.db.product.product_group.delete', $uuid));

        $api->assertStatus(404);
    }

    public function test_product_group_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.db.product.product_group.delete', null));
    }

    #endregion

    #region others

    #endregion
}