<?php

namespace Tests\Feature\API\PurchaseReturnAdditionalCostAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\PurchaseReturnAdditionalCost;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseReturnAdditionalCostAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_purchase_return_additional_cost_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseReturnAdditionalCost = PurchaseReturnAdditionalCost::factory()->for($company)->create();

        $purchaseReturnAdditionalCostArr = PurchaseReturnAdditionalCost::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_return_additional_cost.edit', $purchaseReturnAdditionalCost->ulid), $purchaseReturnAdditionalCostArr);

        $api->assertStatus(401);
    }

    public function test_purchase_return_additional_cost_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseReturnAdditionalCost = PurchaseReturnAdditionalCost::factory()->for($company)->create();

        $purchaseReturnAdditionalCostArr = PurchaseReturnAdditionalCost::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_return_additional_cost.edit', $purchaseReturnAdditionalCost->ulid), $purchaseReturnAdditionalCostArr);

        $api->assertStatus(403);
    }

    public function test_purchase_return_additional_cost_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_return_additional_cost_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_return_additional_cost_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseReturnAdditionalCost = PurchaseReturnAdditionalCost::factory()->for($company)->create();

        $purchaseReturnAdditionalCostArr = PurchaseReturnAdditionalCost::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_return_additional_cost.edit', $purchaseReturnAdditionalCost->ulid), $purchaseReturnAdditionalCostArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('purchase_return_additional_costs', [
            'id' => $purchaseReturnAdditionalCost->id,
            'company_id' => $company->id,
            'code' => $purchaseReturnAdditionalCostArr['code'],
            'name' => $purchaseReturnAdditionalCostArr['name'],
        ]);
    }

    public function test_purchase_return_additional_cost_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_return_additional_cost_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        PurchaseReturnAdditionalCost::factory()->for($company)->count(2)->create();

        $purchaseReturnAdditionalCosts = $company->purchaseReturnAdditionalCosts()->inRandomOrder()->take(2)->get();
        $purchaseReturnAdditionalCost_1 = $purchaseReturnAdditionalCosts[0];
        $purchaseReturnAdditionalCost_2 = $purchaseReturnAdditionalCosts[1];

        $purchaseReturnAdditionalCostArr = PurchaseReturnAdditionalCost::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $purchaseReturnAdditionalCost_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_return_additional_cost.edit', $purchaseReturnAdditionalCost_2->ulid), $purchaseReturnAdditionalCostArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_purchase_return_additional_cost_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        PurchaseReturnAdditionalCost::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $purchaseReturnAdditionalCost_2 = PurchaseReturnAdditionalCost::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $purchaseReturnAdditionalCostArr = PurchaseReturnAdditionalCost::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_return_additional_cost.edit', $purchaseReturnAdditionalCost_2->ulid), $purchaseReturnAdditionalCostArr);

        $api->assertSuccessful();
    }
}
