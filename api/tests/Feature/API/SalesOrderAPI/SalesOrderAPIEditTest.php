<?php

namespace Tests\Feature\API\SalesOrderAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Role;
use App\Models\SalesOrder;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class SalesOrderAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_sales_order_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $salesOrder = SalesOrder::factory()->for($company)->create();

        $salesOrderArr = SalesOrder::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sales_order.edit', $salesOrder->ulid), $salesOrderArr);

        $api->assertStatus(401);
    }

    public function test_sales_order_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $salesOrder = SalesOrder::factory()->for($company)->create();

        $salesOrderArr = SalesOrder::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sales_order.edit', $salesOrder->ulid), $salesOrderArr);

        $api->assertStatus(403);
    }

    public function test_sales_order_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_sales_order_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_sales_order_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $salesOrder = SalesOrder::factory()->for($company)->create();

        $salesOrderArr = SalesOrder::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sales_order.edit', $salesOrder->ulid), $salesOrderArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('sales_orders', [
            'id' => $salesOrder->id,
            'company_id' => $company->id,
            'code' => $salesOrderArr['code'],
            'name' => $salesOrderArr['name'],
        ]);
    }

    public function test_sales_order_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_sales_order_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        SalesOrder::factory()->for($company)->count(2)->create();

        $salesOrders = $company->salesOrders()->inRandomOrder()->take(2)->get();
        $salesOrder_1 = $salesOrders[0];
        $salesOrder_2 = $salesOrders[1];

        $salesOrderArr = SalesOrder::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $salesOrder_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sales_order.edit', $salesOrder_2->ulid), $salesOrderArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_sales_order_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        SalesOrder::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $salesOrder_2 = SalesOrder::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $salesOrderArr = SalesOrder::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sales_order.edit', $salesOrder_2->ulid), $salesOrderArr);

        $api->assertSuccessful();
    }
}
