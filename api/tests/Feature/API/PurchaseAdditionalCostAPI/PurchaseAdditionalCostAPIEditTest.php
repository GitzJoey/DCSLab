<?php

namespace Tests\Feature\API\PurchaseAdditionalCostAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\PurchaseAdditionalCost;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseAdditionalCostAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_purchase_additional_cost_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseAdditionalCost = PurchaseAdditionalCost::factory()->for($company)->create();

        $purchaseAdditionalCostArr = PurchaseAdditionalCost::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_additional_cost.edit', $purchaseAdditionalCost->ulid), $purchaseAdditionalCostArr);

        $api->assertStatus(401);
    }

    public function test_purchase_additional_cost_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseAdditionalCost = PurchaseAdditionalCost::factory()->for($company)->create();

        $purchaseAdditionalCostArr = PurchaseAdditionalCost::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_additional_cost.edit', $purchaseAdditionalCost->ulid), $purchaseAdditionalCostArr);

        $api->assertStatus(403);
    }

    public function test_purchase_additional_cost_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_additional_cost_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_additional_cost_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseAdditionalCost = PurchaseAdditionalCost::factory()->for($company)->create();

        $purchaseAdditionalCostArr = PurchaseAdditionalCost::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_additional_cost.edit', $purchaseAdditionalCost->ulid), $purchaseAdditionalCostArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('purchase_additional_costs', [
            'id' => $purchaseAdditionalCost->id,
            'company_id' => $company->id,
            'code' => $purchaseAdditionalCostArr['code'],
            'name' => $purchaseAdditionalCostArr['name'],
        ]);
    }

    public function test_purchase_additional_cost_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_additional_cost_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        PurchaseAdditionalCost::factory()->for($company)->count(2)->create();

        $purchaseAdditionalCosts = $company->purchaseAdditionalCosts()->inRandomOrder()->take(2)->get();
        $purchaseAdditionalCost_1 = $purchaseAdditionalCosts[0];
        $purchaseAdditionalCost_2 = $purchaseAdditionalCosts[1];

        $purchaseAdditionalCostArr = PurchaseAdditionalCost::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $purchaseAdditionalCost_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_additional_cost.edit', $purchaseAdditionalCost_2->ulid), $purchaseAdditionalCostArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_purchase_additional_cost_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        PurchaseAdditionalCost::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $purchaseAdditionalCost_2 = PurchaseAdditionalCost::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $purchaseAdditionalCostArr = PurchaseAdditionalCost::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase_additional_cost.edit', $purchaseAdditionalCost_2->ulid), $purchaseAdditionalCostArr);

        $api->assertSuccessful();
    }
}
