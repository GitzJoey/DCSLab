<?php

namespace Tests\Feature\API\PurchaseOrderAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class PurchaseOrderAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_purchase_order_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseOrder = PurchaseOrder::factory()->for($company)->create();

        $purchaseOrderArr = PurchaseOrder::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase_order.purchase_order.edit', $purchaseOrder->ulid), $purchaseOrderArr);

        $api->assertStatus(401);
    }

    public function test_purchase_order_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseOrder = PurchaseOrder::factory()->for($company)->create();

        $purchaseOrderArr = PurchaseOrder::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase_order.purchase_order.edit', $purchaseOrder->ulid), $purchaseOrderArr);

        $api->assertStatus(403);
    }

    public function test_purchase_order_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_order_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_order_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $purchaseOrder = PurchaseOrder::factory()->for($company)->create();

        $purchaseOrderArr = PurchaseOrder::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase_order.purchase_order.edit', $purchaseOrder->ulid), $purchaseOrderArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $purchaseOrder->id,
            'company_id' => $company->id,
            'code' => $purchaseOrderArr['code'],
            'name' => $purchaseOrderArr['name'],
        ]);
    }

    public function test_purchase_order_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_purchase_order_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        PurchaseOrder::factory()->for($company)->count(2)->create();

        $purchaseOrders = $company->purchaseOrders()->inRandomOrder()->take(2)->get();
        $purchaseOrder_1 = $purchaseOrders[0];
        $purchaseOrder_2 = $purchaseOrders[1];

        $purchaseOrderArr = PurchaseOrder::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $purchaseOrder_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase_order.purchase_order.edit', $purchaseOrder_2->ulid), $purchaseOrderArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_purchase_order_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        PurchaseOrder::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $purchaseOrder_2 = PurchaseOrder::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $purchaseOrderArr = PurchaseOrder::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.purchase_order.purchase_order.edit', $purchaseOrder_2->ulid), $purchaseOrderArr);

        $api->assertSuccessful();
    }
}
