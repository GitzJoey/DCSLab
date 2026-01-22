<?php

namespace Tests\Feature\API\CustomerGroupAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\CustomerGroup;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class CustomerGroupAPIReadTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_customer_group_api_call_read_any_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        CustomerGroup::factory()->for($company)->create();

        $api = $this->getJson(route('api.get.customer_group.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'refresh' => true,
            'paginate' => [
                'page' => 1,
                'per_page' => 10,
            ],
        ]));

        $api->assertStatus(401);
    }

    public function test_customer_group_api_call_read_any_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        CustomerGroup::factory()->for($company)->create();

        $api = $this->getJson(route('api.get.customer_group.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'refresh' => true,
            'paginate' => [
                'page' => 1,
                'per_page' => 10,
            ],
        ]));

        $api->assertStatus(403);
    }

    public function test_customer_group_api_call_read_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $customerGroup = CustomerGroup::factory()->for($company)->create();

        $api = $this->getJson(route('api.get.customer_group.read', $customerGroup->ulid));

        $api->assertStatus(401);
    }

    public function test_customer_group_api_call_read_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $customerGroup = CustomerGroup::factory()->for($company)->create();

        $api = $this->getJson(route('api.get.customer_group.read', $customerGroup->ulid));

        $api->assertStatus(403);
    }

    public function test_customer_group_api_call_read_with_sql_injection_expect_injection_ignored()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        CustomerGroup::factory()->for($company)->create();

        $injections = [
            "' OR '1'='1",
            '1 UNION SELECT username, password FROM users',
            '1 OR SLEEP(5)',
            '1; DROP TABLE users',
            "admin'--",
            "1' OR '1'='1' UNION SELECT username, password FROM users",
        ];

        foreach ($injections as $injection) {
            $api = $this->getJson(route('api.get.customer_group.read_any', [
                'with_trashed' => false,
                'company_id' => Hashids::encode($company->id),
                'search' => $injection,
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

            $api = $this->getJson(route('api.get.customer_group.read_any', [
                'with_trashed' => false,
                'company_id' => Hashids::encode($company->id),
                'search' => $injection,
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
    }

    public function test_customer_group_api_call_read_any_with_or_without_pagination_expect_paginator_or_collection()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        CustomerGroup::factory()->for($company)->create();

        $api = $this->getJson(route('api.get.customer_group.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
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

        $api = $this->getJson(route('api.get.customer_group.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'refresh' => true,
            'get' => [
                'limit' => 10,
            ],
        ]));

        $api->assertSuccessful();
    }

    public function test_customer_group_api_call_read_any_with_pagination_expect_several_per_page()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        CustomerGroup::factory()->for($company)->create();

        $api = $this->getJson(route('api.get.customer_group.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
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

    public function test_customer_group_api_call_read_any_with_search_expect_filtered_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        CustomerGroup::factory()->for($company)
            ->count(2)->create();

        CustomerGroup::factory()->for($company)
            ->insertStringInName('testing')
            ->count(3)->create();

        $api = $this->getJson(route('api.get.customer_group.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => 'testing',
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
            'total' => 3,
        ]);
    }

    public function test_customer_group_api_call_read_any_without_search_querystring_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        CustomerGroup::factory()->for($company)->create();

        $api = $this->getJson(route('api.get.customer_group.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'refresh' => true,
        ]));

        $api->assertStatus(422);
    }

    public function test_customer_group_api_call_read_any_with_special_char_in_search_expect_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        CustomerGroup::factory()->for($company)->create();

        $api = $this->getJson(route('api.get.customer_group.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => " !#$%&'()*+,-./:;<=>?@[\\]^_`{|}~",
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

    public function test_customer_group_api_call_read_any_with_negative_value_in_parameters_expect_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        CustomerGroup::factory()->for($company)->create();

        $api = $this->getJson(route('api.get.customer_group.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'refresh' => false,
            'paginate' => [
                'page' => -1,
                'per_page' => -25,
            ],
        ]));

        $api->assertStatus(422);
    }

    public function test_customer_group_api_call_read_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $customerGroup = CustomerGroup::factory()->for($company)->create();

        $api = $this->getJson(route('api.get.customer_group.read', $customerGroup->ulid));

        $api->assertSuccessful();
    }

    public function test_customer_group_api_call_read_without_ulid_expect_exception()
    {
        $this->expectException(Exception::class);
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $this->getJson(route('api.get.customer_group.read', null));
    }

    public function test_customer_group_api_call_read_with_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $ulid = Str::ulid()->generate();

        $api = $this->getJson(route('api.get.customer_group.read', $ulid));

        $api->assertStatus(404);
    }
}
