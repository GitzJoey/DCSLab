<?php

namespace Tests\Feature\API;

use Exception;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Tests\APITestCase;
use App\Models\Company;
use App\Enums\UserRoles;
use Dotenv\Parser\Value;
use App\Enums\UnitCategory;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\Factories\Sequence;

class UnitAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /* #region store */
    public function test_unit_api_call_store_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::POS_OWNER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        $this->actingAs($user);

        $unitArr = Unit::factory()->make([
            'company_id' => Hashids::encode($companyId),
        ])->toArray();
        $unitArr['category'] = $this->faker->randomElement(UnitCategory::toArrayEnum())->name;

        $api = $this->json('POST', route('api.post.db.product.unit.save'), $unitArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('units', [
            'company_id' => Hashids::decode($unitArr['company_id'])[0],
            'code' => $unitArr['code'],
            'name' => $unitArr['name'],
            'description' => $unitArr['description'],
            'category' => UnitCategory::fromName($unitArr['category']),
        ]);
    }

    public function test_unit_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::POS_OWNER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        Unit::factory()->create([
            'company_id' => $companyId,
            'code' => 'test1',
        ]);

        $this->actingAs($user);

        $unitArr = Unit::factory()->make([
            'company_id' => Hashids::encode($companyId),
            'code' => 'test1',
        ])->toArray();
        $unitArr['category'] = $this->faker->randomElement(UnitCategory::toArrayEnum())->name;

        $api = $this->json('POST', route('api.post.db.product.unit.save'), $unitArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_unit_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::POS_OWNER->value)->first())
                ->has(Company::factory()->setIsDefault(), 'companies')
                ->create();

        $this->actingAs($user);

        $unitArr = [];
        $api = $this->json('POST', route('api.post.db.product.unit.save'), $unitArr);

        $api->assertJsonValidationErrors(['company_id', 'code', 'name']);
    }
    /* #endregion */

    /* #region list */
    public function test_unit_api_call_list_with_or_without_pagination_expect_paginator_or_collection()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::POS_OWNER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Unit::factory()->count(15), 'units'), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.product.unit.list', [
            'companyId' => Hashids::encode($companyId),
            'category' => $this->faker->randomElement(UnitCategory::toArrayEnum())->name,
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

        $api = $this->getJson(route('api.get.db.product.unit.list', [
            'companyId' => Hashids::encode($companyId),
            'category' => $this->faker->randomElement(UnitCategory::toArrayEnum())->name,
            'search' => '',
            'paginate' => false,
            'page' => 1,
            'perPage' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
    }

    public function test_unit_api_call_list_with_search_expect_filtered_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::POS_OWNER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        Unit::factory()->insertStringInName('testing')->count(10)->create([
            'company_id' => $companyId,
            'category' => 3,
        ]);

        Unit::factory()->count(10)->create([
            'company_id' => $companyId,
            'category' => 3,
        ]);

        $this->actingAs($user);

        $category = UnitCategory::PRODUCTS_AND_SERVICES->name;

        $api = $this->getJson(route('api.get.db.product.unit.list', [
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

    public function test_unit_api_call_list_without_search_querystring_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::POS_OWNER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Unit::factory()->count(2), 'units'), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        $this->actingAs($user);

        $category = UnitCategory::PRODUCTS_AND_SERVICES->name;

        $api = $this->getJson(route('api.get.db.product.unit.list', [
            'companyId' => Hashids::encode($companyId),
            'category' => $category,
        ]));

        $api->assertStatus(422);
    }

    public function test_unit_api_call_list_with_special_char_in_search_expect_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::POS_OWNER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Unit::factory()->count(5), 'units'), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        $this->actingAs($user);

        $category = UnitCategory::PRODUCTS_AND_SERVICES->name;

        $api = $this->getJson(route('api.get.db.product.unit.list', [
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

    public function test_unit_api_call_list_with_negative_value_in_parameters_expect_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::POS_OWNER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Unit::factory()->count(5), 'units'), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        $this->actingAs($user);

        $category = UnitCategory::PRODUCTS_AND_SERVICES->name;

        $api = $this->getJson(route('api.get.db.product.unit.list', [
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
    /* #endregion */

    /* #region read */
    public function test_unit_api_call_read_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::POS_OWNER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Unit::factory()->count(5), 'units'), 'companies')
                    ->create();

        $company = $user->companies->first();

        $this->actingAs($user);

        $uuid = $company->units()->inRandomOrder()->first()->uuid;

        $api = $this->getJson(route('api.get.db.product.unit.read', $uuid));

        $api->assertSuccessful();
    }

    public function test_unit_api_call_read_without_uuid_expect_exception()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::POS_OWNER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);

        $this->getJson(route('api.get.db.product.unit.read', null));
    }

    public function test_unit_api_call_read_with_nonexistance_uuid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::POS_OWNER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Unit::factory()->count(5), 'units'), 'companies')
                    ->create();

        $this->actingAs($user);

        $uuid = $this->faker->uuid();

        $api = $this->getJson(route('api.get.db.product.unit.read', $uuid));

        $api->assertStatus(404);
    }
    /* #endregion */

    /* #region update */
    public function test_unit_api_call_update_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::POS_OWNER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Unit::factory()->count(5), 'units'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $unit = $company->units()->inRandomOrder()->first();
        $unitArr = array_merge([
            'company_id' => Hashids::encode($companyId),
        ], Unit::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.product.unit.edit', $unit->uuid), $unitArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('units', [
            'id' => $unit->id,
            'company_id' => $companyId,
            'code' => $unitArr['code'],
            'name' => $unitArr['name'],
            'description' => $unitArr['description'],
            'category' => $unitArr['category'],
        ]);
    }

    public function test_unit_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::POS_OWNER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Unit::factory()->count(5), 'units'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $units = $company->units()->inRandomOrder()->take(2)->get();
        $unit_1 = $units[0];
        $unit_2 = $units[1];

        $unitArr = array_merge([
            'company_id' => Hashids::encode($companyId),
        ], Unit::factory()->make([
            'code' => $unit_1->code,
        ])->toArray());

        $api = $this->json('POST', route('api.post.db.product.unit.edit', $unit_2->uuid), $unitArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_unit_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::POS_OWNER->value)->first())
                    ->has(Company::factory()->count(2)->state(new Sequence(['default' => true], ['default' => false])), 'companies')
                    ->create();

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;

        Unit::factory()->create([
            'company_id' => $companyId_1,
            'code' => 'test1',
        ]);

        Unit::factory()->create([
            'company_id' => $companyId_2,
            'code' => 'test2',
        ]);

        $this->actingAs($user);

        $unitArr = array_merge([
            'company_id' => Hashids::encode($companyId_2),
        ], Unit::factory()->make([
            'code' => 'test1',
        ])->toArray());

        $api = $this->json('POST', route('api.post.db.product.unit.edit', $company_2->units()->first()->uuid), $unitArr);

        $api->assertSuccessful();
    }
    /* #endregion */

    /* #region delete */
    public function test_unit_api_call_delete_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::POS_OWNER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Unit::factory()->count(5), 'units'), 'companies')
                    ->create();

        $company = $user->companies->first();

        $unit = $company->units()->inRandomOrder()->first();

        $this->actingAs($user);

        $api = $this->json('POST', route('api.post.db.product.unit.delete', $unit->uuid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('units', [
            'id' => $unit->id,
        ]);
    }

    public function test_unit_api_call_delete_of_nonexistance_uuid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $uuid = $this->faker->uuid();

        $api = $this->json('POST', route('api.post.db.product.unit.delete', $uuid));

        $api->assertStatus(404);
    }

    public function test_unit_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.db.product.unit.delete', null));
    }
    /* #endregion */

    /* #region others */

    /* #endregion */
}