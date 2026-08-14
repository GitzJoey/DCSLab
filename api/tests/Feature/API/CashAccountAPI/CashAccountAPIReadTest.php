<?php

namespace Tests\Feature\API\CashAccountAPI;

use App\Enums\UserRolesEnum;
use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class CashAccountAPIReadTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_cash_account_api_call_read_any_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        CashAccount::factory()->for($company)->create([
            'branch_id' => $branch->id,
        ]);

        $api = $this->getJson(route('api.get.cash_account.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'refresh' => true,
            'paginate' => [
                'page' => 1,
                'per_page' => 10,
            ],
        ]));

        $api->assertStatus(401);
    }

    public function test_cash_account_api_call_read_any_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        CashAccount::factory()->for($company)->create([
            'branch_id' => $branch->id,
        ]);

        $api = $this->getJson(route('api.get.cash_account.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'refresh' => true,
            'paginate' => [
                'page' => 1,
                'per_page' => 10,
            ],
        ]));

        $api->assertStatus(403);
    }

    public function test_cash_account_api_call_read_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        $cashAccount = CashAccount::factory()->for($company)->create([
            'branch_id' => $branch->id,
        ]);

        $ulid = $cashAccount->ulid;

        $api = $this->getJson(route('api.get.cash_account.read', $ulid));

        $api->assertStatus(401);
    }

    public function test_cash_account_api_call_read_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        $cashAccount = CashAccount::factory()->for($company)->create([
            'branch_id' => $branch->id,
        ]);

        $ulid = $cashAccount->ulid;

        $api = $this->getJson(route('api.get.cash_account.read', $ulid));

        $api->assertStatus(403);
    }

    public function test_cash_account_api_call_read_with_sql_injection_expect_injection_ignored()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        CashAccount::factory()->for($company)->create([
            'branch_id' => $branch->id,
        ]);

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

        $api = $this->getJson(route('api.get.cash_account.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => $injections[$testIdx],
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

        $api = $this->getJson(route('api.get.cash_account.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => $injections[$testIdx],
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

    public function test_cash_account_api_call_read_any_with_or_without_pagination_expect_paginator_or_collection()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        CashAccount::factory()->for($company)->create([
            'branch_id' => $branch->id,
        ]);

        $api = $this->getJson(route('api.get.cash_account.read_any', [
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

        $api = $this->getJson(route('api.get.cash_account.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
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

    public function test_cash_account_api_call_read_any_with_search_expect_filtered()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        $cashAccount = CashAccount::factory()->for($company)->create([
            'name' => 'Searchable Name',
            'branch_id' => $branch->id,
        ]);

        CashAccount::factory()->for($company)->create([
            'name' => 'Other Name',
            'branch_id' => $branch->id,
        ]);

        $api = $this->getJson(route('api.get.cash_account.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => 'Searchable',
            'refresh' => true,
            'get' => [
                'limit' => 10,
            ],
        ]));

        $api->assertSuccessful();
        $api->assertJsonFragment([
            'name' => 'Searchable Name',
        ]);
        $api->assertJsonMissing([
            'name' => 'Other Name',
        ]);
    }

    public function test_cash_account_api_call_read_any_with_branch_filter_expect_filtered()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branches = $company->branches()->inRandomOrder()->take(2)->get();

        if ($branches->count() < 2) {
            $branch_1 = $branches->first();
            $branch_2 = Branch::factory()->for($company)->create();
        } else {
            $branch_1 = $branches[0];
            $branch_2 = $branches[1];
        }

        CashAccount::factory()->for($company)->create([
            'name' => 'Account Branch 1',
            'branch_id' => $branch_1->id,
        ]);

        CashAccount::factory()->for($company)->create([
            'name' => 'Account Branch 2',
            'branch_id' => $branch_2->id,
        ]);

        $api = $this->getJson(route('api.get.cash_account.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch_1->id),
            'refresh' => true,
            'get' => [
                'limit' => 10,
            ],
        ]));

        $api->assertSuccessful();
        $api->assertJsonFragment([
            'name' => 'Account Branch 1',
        ]);
        $api->assertJsonMissing([
            'name' => 'Account Branch 2',
        ]);
    }

    public function test_cash_account_api_call_read_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        $cashAccount = CashAccount::factory()->for($company)->create([
            'branch_id' => $branch->id,
        ]);

        $ulid = $cashAccount->ulid;

        $api = $this->getJson(route('api.get.cash_account.read', $ulid));

        $api->assertSuccessful();
        $api->assertJsonFragment([
            'name' => $cashAccount->name,
        ]);
    }

    public function test_cash_account_api_call_read_of_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $ulid = Str::ulid()->generate();

        $api = $this->getJson(route('api.get.cash_account.read', $ulid));

        $api->assertStatus(404);
    }
}
