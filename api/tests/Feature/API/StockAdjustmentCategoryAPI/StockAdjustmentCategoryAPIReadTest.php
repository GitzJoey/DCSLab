<?php

namespace Tests\Feature\API\StockAdjustmentCategoryAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Role;
use App\Models\StockAdjustmentCategory;
use App\Models\User;
use Illuminate\Support\Str;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class StockAdjustmentCategoryAPIReadTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_stock_adjustment_category_api_call_read_any_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        StockAdjustmentCategory::factory()->for($company)->create();

        $api = $this->getJson(route('api.get.stock_adjustment_category.read_any', [
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

    public function test_stock_adjustment_category_api_call_read_any_without_access_right_expect_forbidden_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        StockAdjustmentCategory::factory()->for($company)->create();

        $api = $this->getJson(route('api.get.stock_adjustment_category.read_any', [
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

    public function test_stock_adjustment_category_api_call_read_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $stockAdjustmentCategory = StockAdjustmentCategory::factory()->for($company)->create();

        $ulid = $stockAdjustmentCategory->ulid;

        $api = $this->getJson(route('api.get.stock_adjustment_category.read', $ulid));

        $api->assertStatus(401);
    }

    public function test_stock_adjustment_category_api_call_read_without_access_right_expect_forbidden_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $stockAdjustmentCategory = StockAdjustmentCategory::factory()->for($company)->create();

        $ulid = $stockAdjustmentCategory->ulid;

        $api = $this->getJson(route('api.get.stock_adjustment_category.read', $ulid));

        $api->assertStatus(403);
    }

    public function test_stock_adjustment_category_api_call_read_any_with_sql_injection_expect_injection_ignored()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        StockAdjustmentCategory::factory()->for($company)->create();

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

        $api = $this->getJson(route('api.get.stock_adjustment_category.read_any', [
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

        $api = $this->getJson(route('api.get.stock_adjustment_category.read_any', [
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

    public function test_stock_adjustment_category_api_call_read_any_with_or_without_pagination_expect_paginator_or_collection()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        StockAdjustmentCategory::factory()->for($company)->create();

        $api = $this->getJson(route('api.get.stock_adjustment_category.read_any', [
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

        $api = $this->getJson(route('api.get.stock_adjustment_category.read_any', [
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

    public function test_stock_adjustment_category_api_call_read_any_with_search_expect_filtered()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        StockAdjustmentCategory::factory()->for($company)->create([
            'name' => 'Adjustment Category 1',
        ]);

        StockAdjustmentCategory::factory()->for($company)->create([
            'name' => 'Another Category',
        ]);

        $api = $this->getJson(route('api.get.stock_adjustment_category.read_any', [
            'with_trashed' => false,
            'company_id' => Hashids::encode($company->id),
            'search' => 'Adjustment Category 1',
            'refresh' => true,
            'get' => [
                'limit' => 10,
            ],
        ]));

        $api->assertSuccessful();
        $api->assertJsonFragment([
            'name' => 'Adjustment Category 1',
        ]);
        $api->assertJsonMissing([
            'name' => 'Another Category',
        ]);
    }

    public function test_stock_adjustment_category_api_call_read_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $stockAdjustmentCategory = StockAdjustmentCategory::factory()->for($company)->create();

        $ulid = $stockAdjustmentCategory->ulid;

        $api = $this->getJson(route('api.get.stock_adjustment_category.read', $ulid));

        $api->assertSuccessful();
        $api->assertJsonFragment([
            'name' => $stockAdjustmentCategory->name,
        ]);
    }

    public function test_stock_adjustment_category_api_call_read_of_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $ulid = Str::ulid()->generate();

        $api = $this->getJson(route('api.get.stock_adjustment_category.read', $ulid));

        $api->assertStatus(404);
    }
}
