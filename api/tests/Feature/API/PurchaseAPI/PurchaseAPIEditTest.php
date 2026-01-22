<?php

namespace Tests\Feature\API\PurchaseAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Purchase;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_purchase_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchase = Purchase::factory()->for($company)->create();

        $purchaseArr = Purchase::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase.edit', $purchase->ulid), $purchaseArr);

        $api->assertStatus(401);
    }

    public function test_purchase_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $purchase = Purchase::factory()->for($company)->create();

        $purchaseArr = Purchase::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase.edit', $purchase->ulid), $purchaseArr);

        $api->assertStatus(403);
    }

    public function test_purchase_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $purchase = Purchase::factory()->for($company)->create();

        $purchaseArr = Purchase::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase.edit', $purchase->ulid), $purchaseArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'company_id' => $company->id,
            'code' => $purchaseArr['code'],
            'name' => $purchaseArr['name'],
        ]);
    }

    public function test_purchase_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        Purchase::factory()->for($company)->count(2)->create();

        $purchases = $company->purchases()->inRandomOrder()->take(2)->get();
        $purchase_1 = $purchases[0];
        $purchase_2 = $purchases[1];

        $purchaseArr = Purchase::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $purchase_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase.edit', $purchase_2->ulid), $purchaseArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_purchase_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        Purchase::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $purchase_2 = Purchase::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $purchaseArr = Purchase::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase.purchase.edit', $purchase_2->ulid), $purchaseArr);

        $api->assertSuccessful();
    }
}
