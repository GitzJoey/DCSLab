<?php

namespace Tests\Feature\API\CustomerAPI;

use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerGroup;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class CustomerAPIReadTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_customer_api_call_read_any_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory()->count(3))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        Customer::factory()->for($company)->for($customerGroup);

        $api = $this->getJson(route('api.get.db.customer.customer.read_any', [
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'paginate' => true,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertStatus(401);
    }

    public function test_customer_api_call_read_any_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory()->count(3))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        Customer::factory()->for($company)->for($customerGroup);

        $api = $this->getJson(route('api.get.db.customer.customer.read_any', [
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'paginate' => true,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertStatus(403);
    }

    public function test_customer_api_call_reads_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory()->count(3))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        $customer = Customer::factory()->for($company)->for($customerGroup);
        for ($i = 0; $i < 3; $i++) {
            $customer = $customer->has(CustomerAddress::factory()->for($company));
        }
        $customer = $customer->create();

        $api = $this->getJson(route('api.get.db.customer.customer.read', $customer->ulid));

        $api->assertStatus(401);
    }

    public function test_customer_api_call_reads_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory()->count(3))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        $customer = Customer::factory()->for($company)->for($customerGroup);
        for ($i = 0; $i < 3; $i++) {
            $customer = $customer->has(CustomerAddress::factory()->for($company));
        }
        $customer = $customer->create();

        $api = $this->getJson(route('api.get.db.customer.customer.read', $customer->ulid));

        $api->assertStatus(403);
    }

    public function test_customer_api_call_read_any_with_or_without_pagination_expect_paginator_or_collection()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory()->count(3))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        Customer::factory()->for($company)->for($customerGroup);

        $api = $this->getJson(route('api.get.db.customer.customer.read_any', [
            'company_id' => Hashids::encode($company->id),
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
    }

    public function test_customer_api_call_read_any_with_pagination_expect_several_per_page()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.db.customer.customer.read_any', [
            'company_id' => Hashids::encode($company->id),
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

    public function test_customer_api_call_read_any_with_search_expect_filtered_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory()->count(3))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        Customer::factory()
            ->for($company)->for($customerGroup)
            ->has(
                CustomerAddress::factory()
                    ->for($company)
                    ->count(random_int(1, 5))
            )->count(2)->create();

        Customer::factory()
            ->for($company)->for($customerGroup)
            ->insertStringInName('testing')
            ->has(
                CustomerAddress::factory()
                    ->for($company)
                    ->count(random_int(1, 5))
            )->count(3)->create();

        $api = $this->getJson(route('api.get.db.customer.customer.read_any', [
            'company_id' => Hashids::encode($company->id),
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
            'total' => 3,
        ]);
    }

    public function test_customer_api_call_read_any_without_search_querystring_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory()->count(3))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        Customer::factory()->for($company)->for($customerGroup);

        $api = $this->getJson(route('api.get.db.customer.customer.read_any', [
            'company_id' => Hashids::encode($company->id),
        ]));

        $api->assertStatus(422);
    }

    public function test_customer_api_call_read_any_with_special_char_in_search_expect_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory()->count(3))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        Customer::factory()->for($company)->for($customerGroup);

        $api = $this->getJson(route('api.get.db.customer.customer.read_any', [
            'company_id' => Hashids::encode($company->id),
            'search' => " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~",
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

    public function test_customer_api_call_read_any_with_negative_value_in_parameters_expect_results()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory()->count(3))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        Customer::factory()->for($company)->for($customerGroup);

        $api = $this->getJson(route('api.get.db.customer.customer.read_any', [
            'company_id' => Hashids::encode($company->id),
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

    public function test_customer_api_call_read_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory()->count(3))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        $customer = Customer::factory()->for($company)->for($customerGroup);
        for ($i = 0; $i < 3; $i++) {
            $customer = $customer->has(CustomerAddress::factory()->for($company));
        }
        $customer = $customer->create();

        $api = $this->getJson(route('api.get.db.customer.customer.read', $customer->ulid));

        $api->assertSuccessful();
    }

    public function test_customer_api_call_read_without_ulid_expect_exception()
    {
        $this->expectException(Exception::class);
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory()->count(3))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        $customer = Customer::factory()->for($company)->for($customerGroup);
        for ($i = 0; $i < 3; $i++) {
            $customer = $customer->has(CustomerAddress::factory()->for($company));
        }
        $customer->create();

        $this->getJson(route('api.get.db.customer.customer.read', null));
    }

    public function test_customer_api_call_read_with_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory()->count(3))
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        $customer = Customer::factory()->for($company)->for($customerGroup);
        for ($i = 0; $i < 3; $i++) {
            $customer = $customer->has(CustomerAddress::factory()->for($company));
        }
        $customer->create();

        $ulid = Str::ulid()->generate();

        $api = $this->getJson(route('api.get.db.customer.customer.read', $ulid));

        $api->assertStatus(404);
    }
}
