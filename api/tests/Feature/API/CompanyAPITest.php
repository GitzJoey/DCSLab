<?php

namespace Tests\Feature\API;

use Exception;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use App\Models\Company;
use App\Enums\UserRoles;
use Database\Seeders\CompanyTableSeeder;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\Factories\Sequence;

class CompanyAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /* #region store */
    public function test_company_api_call_store_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory(), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $companyArr = array_merge([
            'company_id' => Hashids::encode($companyId),
        ], Company::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.company.company.save'), $companyArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('companies', [
            'code' => $companyArr['code'],
            'name' => $companyArr['name'],
            'address' => $companyArr['address'],
            'default' => $companyArr['default'],
            'status' => $companyArr['status'],
        ]);
    }

    public function test_company_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                ->has(Company::factory()->setIsDefault(), 'companies')
                ->create();

        $this->actingAs($user);

        $companyArr = [];
        
        $api = $this->json('POST', route('api.post.db.company.company.save'), $companyArr);

        $api->assertJsonValidationErrors(['code', 'name', 'status']);
    }
    /* #endregion */

    /* #region list */
    public function test_company_api_call_list_with_or_without_pagination_expect_paginator_or_collection()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                ->has(Company::factory()->setIsDefault(), 'companies')
                ->create();

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.company.list', [
            'userId' => $user->id,
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

        $api = $this->getJson(route('api.get.db.company.company.list', [
            'userId' => $user->id,
            'search' => '',
            'paginate' => false,
            'page' => 1,
            'perPage' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
    }
    
    public function test_company_api_call_list_with_search_expect_filtered_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->create();

        $userId = $user->id;

        for ($i = 0; $i < 10; $i++) {
            $company = Company::factory()->insertStringInName('testing')->create([]);
            $user->companies()->attach($company->id);
        }

        for ($i = 0; $i < 10; $i++) {
            $company = Company::factory()->create([]);
            $user->companies()->attach($company->id);
        }

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.company.list', [
            'userId' => Hashids::encode($userId),
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

    public function test_company_api_call_list_without_search_querystring_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                ->has(Company::factory()->setIsDefault(), 'companies')
                ->create();

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.company.list', []));

        $api->assertStatus(422);
    }

    public function test_company_api_call_list_with_special_char_in_search_expect_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                ->has(Company::factory()->setIsDefault(), 'companies')
                ->create();

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.company.list', [
            'userId' => $user->id,
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

    public function test_company_api_call_list_with_negative_value_in_parameters_expect_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                ->has(Company::factory()->setIsDefault(), 'companies')
                ->create();

        $companyId = $user->companies->first()->id;

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.company.list', [
            'userId' => $user->id,
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
    public function test_company_api_call_read_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                ->has(Company::factory()->setIsDefault(), 'companies')
                ->create();

        $company = $user->companies->first();

        $this->actingAs($user);

        $uuid = $company->inRandomOrder()->first()->uuid;

        $api = $this->getJson(route('api.get.db.company.company.read', $uuid));

        $api->assertSuccessful();
    }

    public function test_company_api_call_read_without_uuid_expect_exception()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                ->has(Company::factory()->setIsDefault(), 'companies')
                ->create();

        $this->actingAs($user);

        $this->getJson(route('api.get.db.company.company.read', null));
    }

    public function test_company_api_call_read_with_nonexistance_uuid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                ->has(Company::factory()->setIsDefault(), 'companies')
                ->create();

        $this->actingAs($user);

        $uuid = $this->faker->uuid();

        $api = $this->getJson(route('api.get.db.company.company.read', $uuid));

        $api->assertStatus(404);
    }
    /* #endregion */

    /* #region update */
    public function test_company_api_call_update_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                ->has(Company::factory()->setIsDefault(), 'companies')
                ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $companyArr = array_merge([
            'company_id' => Hashids::encode($companyId),
        ], Company::factory()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.company.company.edit', $company->uuid), $companyArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'code' => $companyArr['code'],
            'name' => $companyArr['name'],
            'address' => $companyArr['address'],
            'default' => $companyArr['default'],
            'status' => $companyArr['status'],
        ]);
    }

    public function test_company_api_call_update_and_use_existing_code_in_same_user_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->count(5)->state(new Sequence(['default' => true], ['default' => false])), 'companies')
                    ->create();

        $this->actingAs($user);

        $companies = $user->companies()->take(2)->get();
        $company_1 = $companies[0];
        $company_2 = $companies[1];

        $companyArr = Company::factory()->make([
            'code' => $company_2->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.company.edit', $company_1->uuid), $companyArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }
    /* #endregion */

    /* #region delete */
    public function test_company_api_call_delete_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory(), 'companies')
                    ->create();

        $company = $user->companies->first();

        $this->actingAs($user);

        $api = $this->json('POST', route('api.post.db.company.company.delete', $company->uuid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('companies', [
            'id' => $company->id,
        ]);
    }

    public function test_company_api_call_delete_of_nonexistance_uuid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $uuid = $this->faker->uuid();

        $api = $this->json('POST', route('api.post.db.company.company.delete', $uuid));

        $api->assertStatus(404);
    }

    public function test_company_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.db.company.company.delete', null));
    }
    /* #endregion */

    /* #region others */

    public function test_company_api_call_reset_default_company_expect_reseted()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                ->has(Company::factory()->count(5), 'companies')
                ->create();
        
        $this->actingAs($user);

        $defaultCompany = $user->companies()->inRandomOrder()->first();
        $defaultCompany->default = true;
        $defaultCompany->save();

        $company = $user->companies->first();

        $api = $this->json('GET', route('api.get.db.company.company.read.reset.defaultcompany', $company->uuid));

        $api->assertSuccessful();

        $resultCount = $user->companies()->where('default', '=', true)->count();
        $this->assertTrue($resultCount == 0);
    }

    /* #endregion */
}
