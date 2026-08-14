<?php

namespace Tests\Feature\API\StockTransferAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Role;
use App\Models\StockTransfer;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class StockTransferAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_stock_transfer_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $stockTransfer = StockTransfer::factory()->for($company)->create();

        $stockTransferArr = StockTransfer::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.stock_transfer.stock_transfer.edit', $stockTransfer->ulid), $stockTransferArr);

        $api->assertStatus(401);
    }

    public function test_stock_transfer_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $stockTransfer = StockTransfer::factory()->for($company)->create();

        $stockTransferArr = StockTransfer::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.stock_transfer.stock_transfer.edit', $stockTransfer->ulid), $stockTransferArr);

        $api->assertStatus(403);
    }

    public function test_stock_transfer_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_stock_transfer_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_stock_transfer_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $stockTransfer = StockTransfer::factory()->for($company)->create();

        $stockTransferArr = StockTransfer::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.stock_transfer.stock_transfer.edit', $stockTransfer->ulid), $stockTransferArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('stock_transfers', [
            'id' => $stockTransfer->id,
            'company_id' => $company->id,
            'code' => $stockTransferArr['code'],
            'name' => $stockTransferArr['name'],
        ]);
    }

    public function test_stock_transfer_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_stock_transfer_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        StockTransfer::factory()->for($company)->count(2)->create();

        $stockTransfers = $company->stockTransfers()->inRandomOrder()->take(2)->get();
        $stockTransfer_1 = $stockTransfers[0];
        $stockTransfer_2 = $stockTransfers[1];

        $stockTransferArr = StockTransfer::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $stockTransfer_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.stock_transfer.stock_transfer.edit', $stockTransfer_2->ulid), $stockTransferArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_stock_transfer_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        StockTransfer::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $stockTransfer_2 = StockTransfer::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $stockTransferArr = StockTransfer::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.stock_transfer.stock_transfer.edit', $stockTransfer_2->ulid), $stockTransferArr);

        $api->assertSuccessful();
    }
}
