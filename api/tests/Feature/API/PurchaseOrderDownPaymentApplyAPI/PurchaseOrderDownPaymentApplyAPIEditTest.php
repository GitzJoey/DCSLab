<?php

namespace Tests\Feature\API\PurchaseOrderDownPaymentApplyAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\PurchaseOrderDownPaymentApply;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseOrderDownPaymentApplyAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_purchase_order_down_payment_apply_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseOrderDownPaymentApply = PurchaseOrderDownPaymentApply::factory()->for($company)->create();

        $purchaseOrderDownPaymentApplyArr = PurchaseOrderDownPaymentApply::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase_order.purchase_order_down_payment_apply.edit', $purchaseOrderDownPaymentApply->ulid), $purchaseOrderDownPaymentApplyArr);

        $api->assertStatus(401);
    }

    public function test_purchase_order_down_payment_apply_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseOrderDownPaymentApply = PurchaseOrderDownPaymentApply::factory()->for($company)->create();

        $purchaseOrderDownPaymentApplyArr = PurchaseOrderDownPaymentApply::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase_order.purchase_order_down_payment_apply.edit', $purchaseOrderDownPaymentApply->ulid), $purchaseOrderDownPaymentApplyArr);

        $api->assertStatus(403);
    }

    public function test_purchase_order_down_payment_apply_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_order_down_payment_apply_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_order_down_payment_apply_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseOrderDownPaymentApply = PurchaseOrderDownPaymentApply::factory()->for($company)->create();

        $purchaseOrderDownPaymentApplyArr = PurchaseOrderDownPaymentApply::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase_order.purchase_order_down_payment_apply.edit', $purchaseOrderDownPaymentApply->ulid), $purchaseOrderDownPaymentApplyArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('purchase_order_down_payment_applies', [
            'id' => $purchaseOrderDownPaymentApply->id,
            'company_id' => $company->id,
            'code' => $purchaseOrderDownPaymentApplyArr['code'],
            'name' => $purchaseOrderDownPaymentApplyArr['name'],
        ]);
    }

    public function test_purchase_order_down_payment_apply_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_order_down_payment_apply_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        PurchaseOrderDownPaymentApply::factory()->for($company)->count(2)->create();

        $purchaseOrderDownPaymentApplies = $company->purchaseOrderDownPaymentApplies()->inRandomOrder()->take(2)->get();
        $purchaseOrderDownPaymentApply_1 = $purchaseOrderDownPaymentApplies[0];
        $purchaseOrderDownPaymentApply_2 = $purchaseOrderDownPaymentApplies[1];

        $purchaseOrderDownPaymentApplyArr = PurchaseOrderDownPaymentApply::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $purchaseOrderDownPaymentApply_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase_order.purchase_order_down_payment_apply.edit', $purchaseOrderDownPaymentApply_2->ulid), $purchaseOrderDownPaymentApplyArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_purchase_order_down_payment_apply_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        PurchaseOrderDownPaymentApply::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $purchaseOrderDownPaymentApply_2 = PurchaseOrderDownPaymentApply::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $purchaseOrderDownPaymentApplyArr = PurchaseOrderDownPaymentApply::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase_order.purchase_order_down_payment_apply.edit', $purchaseOrderDownPaymentApply_2->ulid), $purchaseOrderDownPaymentApplyArr);

        $api->assertSuccessful();
    }
}
