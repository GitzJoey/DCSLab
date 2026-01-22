<?php

namespace Tests\Feature\API\StockAdjustmentCategoryAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Role;
use App\Models\StockAdjustmentCategory;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Tests\APITestCase;

class StockAdjustmentCategoryAPIDeleteTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_stock_adjustment_category_api_call_delete_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $category = StockAdjustmentCategory::factory()->for($company)->create();

        $api = $this->json('POST', route('api.post.stock_adjustment_category.delete', $category->ulid));

        $api->assertUnauthorized();
    }

    public function test_stock_adjustment_category_api_call_delete_without_access_right_expect_forbidden_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $category = StockAdjustmentCategory::factory()->for($company)->create();

        $api = $this->json('POST', route('api.post.stock_adjustment_category.delete', $category->ulid));

        $api->assertForbidden();
    }

    public function test_stock_adjustment_category_api_call_delete_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $category = StockAdjustmentCategory::factory()->for($company)->create();

        $api = $this->json('POST', route('api.post.stock_adjustment_category.delete', $category->ulid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('stock_adjustment_categories', [
            'id' => $category->id,
        ]);
    }

    public function test_stock_adjustment_category_api_call_delete_of_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $ulid = Str::ulid()->generate();

        $api = $this->json('POST', route('api.post.stock_adjustment_category.delete', $ulid));

        $api->assertStatus(404);
    }

    public function test_stock_adjustment_category_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);

        $user = User::factory()->create();

        $this->actingAs($user);

        $this->json('POST', route('api.post.stock_adjustment_category.delete', null));
    }
}
