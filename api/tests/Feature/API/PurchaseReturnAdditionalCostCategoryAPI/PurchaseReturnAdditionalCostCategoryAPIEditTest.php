<?php

namespace Tests\Feature\API\PurchaseReturnAdditionalCostCategoryAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\PurchaseReturnAdditionalCostCategory;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseReturnAdditionalCostCategoryAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_purchase_return_additional_cost_category_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseReturnAdditionalCostCategory = PurchaseReturnAdditionalCostCategory::factory()->for($company)->create();

        $purchaseReturnAdditionalCostCategoryArr = PurchaseReturnAdditionalCostCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_return_additional_cost_category.edit', $purchaseReturnAdditionalCostCategory->ulid), $purchaseReturnAdditionalCostCategoryArr);

        $api->assertStatus(401);
    }

    public function test_purchase_return_additional_cost_category_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseReturnAdditionalCostCategory = PurchaseReturnAdditionalCostCategory::factory()->for($company)->create();

        $purchaseReturnAdditionalCostCategoryArr = PurchaseReturnAdditionalCostCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_return_additional_cost_category.edit', $purchaseReturnAdditionalCostCategory->ulid), $purchaseReturnAdditionalCostCategoryArr);

        $api->assertStatus(403);
    }

    public function test_purchase_return_additional_cost_category_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_return_additional_cost_category_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_return_additional_cost_category_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseReturnAdditionalCostCategory = PurchaseReturnAdditionalCostCategory::factory()->for($company)->create();

        $purchaseReturnAdditionalCostCategoryArr = PurchaseReturnAdditionalCostCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_return_additional_cost_category.edit', $purchaseReturnAdditionalCostCategory->ulid), $purchaseReturnAdditionalCostCategoryArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('purchase_return_additional_cost_categories', [
            'id' => $purchaseReturnAdditionalCostCategory->id,
            'company_id' => $company->id,
            'code' => $purchaseReturnAdditionalCostCategoryArr['code'],
            'name' => $purchaseReturnAdditionalCostCategoryArr['name'],
        ]);
    }

    public function test_purchase_return_additional_cost_category_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_return_additional_cost_category_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        PurchaseReturnAdditionalCostCategory::factory()->for($company)->count(2)->create();

        $purchaseReturnAdditionalCostCategories = $company->purchaseReturnAdditionalCostCategories()->inRandomOrder()->take(2)->get();
        $purchaseReturnAdditionalCostCategory_1 = $purchaseReturnAdditionalCostCategories[0];
        $purchaseReturnAdditionalCostCategory_2 = $purchaseReturnAdditionalCostCategories[1];

        $purchaseReturnAdditionalCostCategoryArr = PurchaseReturnAdditionalCostCategory::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $purchaseReturnAdditionalCostCategory_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_return_additional_cost_category.edit', $purchaseReturnAdditionalCostCategory_2->ulid), $purchaseReturnAdditionalCostCategoryArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_purchase_return_additional_cost_category_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        PurchaseReturnAdditionalCostCategory::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $purchaseReturnAdditionalCostCategory_2 = PurchaseReturnAdditionalCostCategory::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $purchaseReturnAdditionalCostCategoryArr = PurchaseReturnAdditionalCostCategory::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_return_additional_cost_category.edit', $purchaseReturnAdditionalCostCategory_2->ulid), $purchaseReturnAdditionalCostCategoryArr);

        $api->assertSuccessful();
    }
}
