<?php

namespace Tests\Feature\API\StockAdjustmentCategoryAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Role;
use App\Models\StockAdjustmentCategory;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class StockAdjustmentCategoryAPICreateTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_stock_adjustment_category_api_call_store_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $payload = StockAdjustmentCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.stock_adjustment_category.save'), $payload);

        $api->assertUnauthorized();
    }

    public function test_stock_adjustment_category_api_call_store_without_access_right_expect_forbidden_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $payload = StockAdjustmentCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.stock_adjustment_category.save'), $payload);

        $api->assertForbidden();
    }

    public function test_stock_adjustment_category_api_call_store_with_script_tags_in_payload_expect_stripped()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $payload = StockAdjustmentCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'name' => '<script>alert("xss")</script>',
        ])->toArray();

        $api = $this->json('POST', route('api.post.stock_adjustment_category.save'), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('stock_adjustment_categories', [
            'company_id' => $company->id,
            'name' => 'alert("xss")',
        ]);
    }

    public function test_stock_adjustment_category_api_call_store_with_script_tags_in_payload_expect_encoded()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $payload = StockAdjustmentCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'name' => '<script>alert("xss")</script>',
        ])->toArray();

        $api = $this->json('POST', route('api.post.stock_adjustment_category.save'), $payload, ['X-Sanitizer-Mode' => 'encode']);

        $api->assertSuccessful();
        $this->assertDatabaseHas('stock_adjustment_categories', [
            'company_id' => $company->id,
            'name' => '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;',
        ]);
    }

    public function test_stock_adjustment_category_api_call_store_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $payload = StockAdjustmentCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.stock_adjustment_category.save'), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('stock_adjustment_categories', [
            'company_id' => $company->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_stock_adjustment_category_api_call_store_with_auto_code_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $payload = StockAdjustmentCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => Config::get('dcslab.KEYWORDS.AUTO'),
        ])->toArray();

        $api = $this->json('POST', route('api.post.stock_adjustment_category.save'), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('stock_adjustment_categories', [
            'company_id' => $company->id,
            'name' => $payload['name'],
        ]);
    }

    public function test_stock_adjustment_category_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        StockAdjustmentCategory::factory()->for($company)->create([
            'code' => 'TEST1',
        ]);

        $payload = StockAdjustmentCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => 'TEST1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.stock_adjustment_category.save'), $payload);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_stock_adjustment_category_api_call_store_with_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->take(2)->get();

        $company_1 = $companies[0];
        $company_2 = $companies[1];

        StockAdjustmentCategory::factory()->for($company_1)->create([
            'code' => 'TEST1',
        ]);

        $payload = StockAdjustmentCategory::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'TEST1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.stock_adjustment_category.save'), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('stock_adjustment_categories', [
            'company_id' => $company_2->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_stock_adjustment_category_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $payload = [];

        $api = $this->json('POST', route('api.post.stock_adjustment_category.save'), $payload);

        $api->assertJsonValidationErrors(['company_id', 'code', 'name']);
    }
}
