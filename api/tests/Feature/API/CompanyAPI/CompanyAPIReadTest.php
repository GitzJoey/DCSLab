<?php

namespace Tests\Feature\API\CompanyAPI;

use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Str;
use Tests\APITestCase;

class CompanyAPIReadTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_company_api_call_read_any_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $api = $this->getJson(route('api.get.db.company.company.read_any', [
            'userId' => $user->id,
            'search' => '',
            'paginate' => true,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertStatus(401);
    }

    public function test_company_api_call_read_any_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.company.read_any', [
            'userId' => $user->id,
            'search' => '',
            'paginate' => true,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertStatus(403);
    }

    public function test_company_api_call_read_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.db.company.company.read', $company->ulid));

        $api->assertStatus(401);
    }

    public function test_company_api_call_read_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.db.company.company.read', $company->ulid));

        $api->assertStatus(403);
    }

    public function test_company_api_call_read_with_sql_injection_expect_injection_ignored()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_company_api_call_read_any_with_or_without_pagination_expect_paginator_or_collection()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.company.read_any', [
            'userId' => $user->id,
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

        $api = $this->getJson(route('api.get.db.company.company.read_any', [
            'userId' => $user->id,
            'search' => '',
            'paginate' => false,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
    }

    public function test_company_api_call_read_any_with_pagination_expect_several_per_page()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.company.read_any', [
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

    public function test_company_api_call_read_any_with_search_expect_filtered_results()
    {
        $companyCount = random_int(1, 4);
        $idxDefaultCompany = random_int(0, $companyCount - 1);
        $idxTest = random_int(0, $companyCount - 1);
        $defaultName = Company::factory()->make()->name;
        $testName = Company::factory()->insertStringInName('testing')->make()->name;

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->count($companyCount)
                ->state(new Sequence(
                    fn (Sequence $sequence) => [
                        'default' => $sequence->index == $idxDefaultCompany ? true : false,
                        'name' => $sequence->index == $idxTest ? $testName : $defaultName,
                    ]
                ))
            )
            ->create();

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.company.read_any', [
            'userId' => $user->id,
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

    public function test_company_api_call_read_any_without_search_querystring_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.company.read_any', []));

        $api->assertStatus(422);
    }

    public function test_company_api_call_read_any_with_special_char_in_search_expect_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.company.read_any', [
            'userId' => $user->id,
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

    public function test_company_api_call_read_any_with_negative_value_in_parameters_expect_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.company.read_any', [
            'userId' => $user->id,
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

    public function test_company_api_call_read_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.db.company.company.read', $company->ulid));

        $api->assertSuccessful();
    }

    public function test_company_api_call_read_without_ulid_expect_exception()
    {
        $this->expectException(Exception::class);
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->create();

        $this->actingAs($user);

        $this->getJson(route('api.get.db.company.company.read', null));
    }

    public function test_company_api_call_read_with_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $ulid = Str::ulid()->generate();

        $api = $this->getJson(route('api.get.db.company.company.read', $ulid));

        $api->assertStatus(404);
    }
}
