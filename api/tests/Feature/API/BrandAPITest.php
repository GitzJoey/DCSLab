<?php

namespace Tests\Feature\API;

use Exception;
use App\Models\Role;
use App\Models\User;
use App\Models\Brand;
use Tests\APITestCase;
use App\Models\Company;
use App\Enums\UserRoles;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\Factories\Sequence;

class BrandAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /* #region store */
        public function test_brand_api_call_store_expect_successful()
        {
            /** @var \Illuminate\Contracts\Auth\Authenticatable */
            $user = User::factory()
                        ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                        ->has(Company::factory()->setIsDefault(), 'companies')
                        ->create();

            $companyId = $user->companies->first()->id;

            $this->actingAs($user);

            $brandArr = array_merge([
                'company_id' => Hashids::encode($companyId),
            ], Brand::factory()->make()->toArray());

            $api = $this->json('POST', route('api.post.db.product.brand.save'), $brandArr);

            $api->assertSuccessful();
            $this->assertDatabaseHas('brands', [
                'company_id' => $companyId,
                'code' => $brandArr['code'],
                'name' => $brandArr['name'],
            ]);
        }

        public function test_brand_api_call_store_with_existing_code_in_same_company_expect_failed()
        {
            /** @var \Illuminate\Contracts\Auth\Authenticatable */
            $user = User::factory()
                        ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                        ->has(Company::factory()->setIsDefault(), 'companies')
                        ->create();

            $company = $user->companies->first();
            $companyId = $company->id;

            Brand::factory()->create([
                'company_id' => $companyId,
                'code' => 'test1',
            ]);

            $this->actingAs($user);

            $brandArr = array_merge([
                'company_id' => Hashids::encode($companyId),
            ], Brand::factory()->make([
                'code' => 'test1',
            ])->toArray());

            $api = $this->json('POST', route('api.post.db.product.brand.save'), $brandArr);

            $api->assertStatus(422);
            $api->assertJsonStructure([
                'errors',
            ]);
        }

        public function test_brand_api_call_store_with_empty_string_parameters_expect_validation_error()
        {
            /** @var \Illuminate\Contracts\Auth\Authenticatable */
            $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

            $this->actingAs($user);

            $brandArr = [];
            $api = $this->json('POST', route('api.post.db.product.brand.save'), $brandArr);

            $api->assertJsonValidationErrors(['company_id', 'name']);
        }

    /* #endregion */

    /* #region list */
        public function test_brand_api_call_list_with_or_without_pagination_expect_paginator_or_collection()
        {
            /** @var \Illuminate\Contracts\Auth\Authenticatable */
            $user = User::factory()
                        ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                        ->has(Company::factory()->setIsDefault()
                                ->has(Brand::factory()->count(15), 'brands'), 'companies')
                        ->create();

            $company = $user->companies->first();
            $companyId = $company->id;

            $this->actingAs($user);

            $api = $this->getJson(route('api.get.db.product.brand.list', [
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

            $api = $this->getJson(route('api.get.db.product.brand.list', [
                'companyId' => Hashids::encode($companyId),
                'search' => '',
                'paginate' => false,
                'page' => 1,
                'perPage' => 10,
                'refresh' => true,
            ]));

            $api->assertSuccessful();
        }

        public function test_brand_api_call_list_with_search_expect_filtered_results()
        {
            /** @var \Illuminate\Contracts\Auth\Authenticatable */
            $user = User::factory()
                        ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                        ->has(Company::factory()->setIsDefault(), 'companies')
                        ->create();

            $company = $user->companies->first();
            $companyId = $company->id;

            Brand::factory()->insertStringInName(' testing')->count(10)->create([
                'company_id' => $companyId
            ]);

            Brand::factory()->count(10)->create([
                'company_id' => $companyId,
            ]);

            $this->actingAs($user);

            $api = $this->getJson(route('api.get.db.product.brand.list', [
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

        public function test_brand_api_call_list_without_search_querystring_expect_failed()
        {
            /** @var \Illuminate\Contracts\Auth\Authenticatable */
            $user = User::factory()
                        ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                        ->has(Company::factory()->setIsDefault()
                                ->has(Brand::factory()->count(2), 'brands'), 'companies')
                        ->create();

            $company = $user->companies->first();
            $companyId = $company->id;

            $this->actingAs($user);

            $api = $this->getJson(route('api.get.db.product.brand.list', [
                'companyId' => Hashids::encode($companyId),
            ]));

            $api->assertStatus(422);
        }

        public function test_brand_api_call_list_with_special_char_in_search_expect_results()
        {
            /** @var \Illuminate\Contracts\Auth\Authenticatable */
            $user = User::factory()
                        ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                        ->has(Company::factory()->setIsDefault()
                                ->has(Brand::factory()->count(5), 'brands'), 'companies')
                        ->create();

            $company = $user->companies->first();
            $companyId = $company->id;

            $this->actingAs($user);

            $api = $this->getJson(route('api.get.db.product.brand.list', [
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

        public function test_brand_api_call_list_with_negative_value_in_parameters_expect_results()
        {
            /** @var \Illuminate\Contracts\Auth\Authenticatable */
            $user = User::factory()
                        ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                        ->has(Company::factory()->setIsDefault()
                                ->has(Brand::factory()->count(5), 'brands'), 'companies')
                        ->create();

            $company = $user->companies->first();
            $companyId = $company->id;

            $this->actingAs($user);

            $api = $this->getJson(route('api.get.db.product.brand.list', [
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
        public function test_brand_api_call_read_expect_successful()
        {
            /** @var \Illuminate\Contracts\Auth\Authenticatable */
            $user = User::factory()
                        ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                        ->has(Company::factory()->setIsDefault()
                                ->has(Brand::factory()->count(5), 'brands'), 'companies')
                        ->create();

            $company = $user->companies->first();
            $companyId = $company->id;

            $this->actingAs($user);

            $uuid = $company->brands()->inRandomOrder()->first()->uuid;

            $api = $this->getJson(route('api.get.db.product.brand.read', $uuid));

            $api->assertSuccessful();
        }

        public function test_brand_api_call_read_without_uuid_expect_exception()
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

            $this->getJson(route('api.get.db.product.brand.read', null));
        }

        public function test_brand_api_call_read_with_nonexistance_uuid_expect_not_found()
        {
            /** @var \Illuminate\Contracts\Auth\Authenticatable */
            $user = User::factory()
                        ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                        ->has(Company::factory()->setIsDefault()
                                ->has(Brand::factory()->count(5), 'brands'), 'companies')
                        ->create();

            $company = $user->companies->first();
            $companyId = $company->id;

            $this->actingAs($user);

            $uuid = $this->faker->uuid();

            $api = $this->getJson(route('api.get.db.product.brand.read', $uuid));

            $api->assertStatus(404);
        }

    /* #endregion */

    /* #region update */

        public function test_brand_api_call_update_expect_successful()
        {
            /** @var \Illuminate\Contracts\Auth\Authenticatable */
            $user = User::factory()
                        ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                        ->has(Company::factory()->setIsDefault()
                                ->has(Brand::factory()->count(5), 'brands'), 'companies')
                        ->create();

            $company = $user->companies->first();
            $companyId = $company->id;

            $this->actingAs($user);

            $brand = $company->brands()->inRandomOrder()->first();
            $brandArr = array_merge([
                'company_id' => Hashids::encode($companyId),
            ], Brand::factory()->make()->toArray());

            $api = $this->json('POST', route('api.post.db.product.brand.edit', $brand->uuid), $brandArr);

            $api->assertSuccessful();
            $this->assertDatabaseHas('brands', [
                'id' => $brand->id,
                'company_id' => $companyId,
                'code' => $brandArr['code'],
                'name' => $brandArr['name'],
            ]);
        }

        public function test_brand_api_call_update_and_use_existing_code_in_same_company_expect_failed()
        {
            /** @var \Illuminate\Contracts\Auth\Authenticatable */
            $user = User::factory()
                        ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                        ->has(Company::factory()->setIsDefault()
                                ->has(Brand::factory()->count(5), 'brands'), 'companies')
                        ->create();

            $company = $user->companies->first();
            $companyId = $company->id;

            $this->actingAs($user);

            $brands = $company->brands()->inRandomOrder()->take(2)->get();
            $brand_1 = $brands[0];
            $brand_2 = $brands[1];

            $brandArr = array_merge([
                'company_id' => Hashids::encode($companyId),
            ], Brand::factory()->make([
                'code' => $brand_1->code,
            ])->toArray());

            $api = $this->json('POST', route('api.post.db.product.brand.edit', $brand_2->uuid), $brandArr);

            $api->assertStatus(422);
            $api->assertJsonStructure([
                'errors',
            ]);
        }

        public function test_brand_api_call_update_and_use_existing_code_in_different_company_expect_successful()
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

            Brand::factory()->create([
                'company_id' => $companyId_1,
                'code' => 'test1',
            ]);

            Brand::factory()->create([
                'company_id' => $companyId_2,
                'code' => 'test2',
            ]);

            $this->actingAs($user);

            $brandArr = array_merge([
                'company_id' => Hashids::encode($companyId_2),
            ], Brand::factory()->make([
                'code' => 'test1',
            ])->toArray());

            $api = $this->json('POST', route('api.post.db.product.brand.edit', $company_2->brands()->first()->uuid), $brandArr);

            $api->assertSuccessful();
        }

    /* #endregion */

    /* #region delete */

        public function test_brand_api_call_delete_expect_successful()
        {
            /** @var \Illuminate\Contracts\Auth\Authenticatable */
            $user = User::factory()
                        ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                        ->has(Company::factory()->setIsDefault()
                                ->has(Brand::factory()->count(5), 'brands'), 'companies')
                        ->create();

            $company = $user->companies->first();
            $companyId = $company->id;

            $brand = $company->brands()->inRandomOrder()->first();

            $this->actingAs($user);

            $api = $this->json('POST', route('api.post.db.product.brand.delete', $brand->uuid));

            $api->assertSuccessful();
            $this->assertSoftDeleted('brands', [
                'id' => $brand->id,
            ]);
        }

        public function test_brand_api_call_delete_of_nonexistance_uuid_expect_not_found()
        {
            /** @var \Illuminate\Contracts\Auth\Authenticatable */
            $user = User::factory()->create();

            $this->actingAs($user);
            $uuid = $this->faker->uuid();

            $api = $this->json('POST', route('api.post.db.product.brand.delete', $uuid));

            $api->assertStatus(404);
        }

        public function test_brand_api_call_delete_without_parameters_expect_failed()
        {
            $this->expectException(Exception::class);
            /** @var \Illuminate\Contracts\Auth\Authenticatable */
            $user = User::factory()->create();

            $this->actingAs($user);
            $api = $this->json('POST', route('api.post.db.product.brand.delete', null));
        }

    /* #endregion */

    /* #region other */

    /* #endregion */
}