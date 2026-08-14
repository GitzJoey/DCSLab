<?php

namespace Tests\Feature\API\StockAdjustmentCategoryAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Role;
use App\Models\StockAdjustmentCategory;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class StockAdjustmentCategoryAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_stock_adjustment_category_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $stockAdjustmentCategory = StockAdjustmentCategory::factory()->for($company)->create();

        $payload = StockAdjustmentCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.stock_adjustment_category.edit', $stockAdjustmentCategory->ulid), $payload);

        $api->assertStatus(401);
    }

    public function test_stock_adjustment_category_api_call_update_without_access_right_expect_forbidden_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $stockAdjustmentCategory = StockAdjustmentCategory::factory()->for($company)->create();

        $payload = StockAdjustmentCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.stock_adjustment_category.edit', $stockAdjustmentCategory->ulid), $payload);

        $api->assertStatus(403);
    }

    public function test_stock_adjustment_category_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $stockAdjustmentCategory = StockAdjustmentCategory::factory()->for($company)->create();

        $payload = StockAdjustmentCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.stock_adjustment_category.edit', $stockAdjustmentCategory->ulid), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('stock_adjustment_categories', [
            'id' => $stockAdjustmentCategory->id,
            'company_id' => $company->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_stock_adjustment_category_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        StockAdjustmentCategory::factory()->for($company)->count(2)->create();

        $categories = $company->stockAdjustmentCategories()->inRandomOrder()->take(2)->get();
        $category1 = $categories[0];
        $category2 = $categories[1];

        $payload = StockAdjustmentCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $category1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.stock_adjustment_category.edit', $category2->ulid), $payload);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_stock_adjustment_category_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company1 = $companies[0];
        StockAdjustmentCategory::factory()->for($company1)->create([
            'code' => 'TEST1',
        ]);

        $company2 = $companies[1];
        $category2 = StockAdjustmentCategory::factory()->for($company2)->create([
            'code' => 'TEST2',
        ]);

        $payload = StockAdjustmentCategory::factory()->make([
            'company_id' => Hashids::encode($company2->id),
            'code' => 'TEST1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.stock_adjustment_category.edit', $category2->ulid), $payload);

        $api->assertSuccessful();
    }
}
