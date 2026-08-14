<?php

namespace Tests\Feature\API\StockTransferItemSerialAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Role;
use App\Models\StockTransferItemSerial;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class StockTransferItemSerialAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_stock_transfer_item_serial_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $stockTransferItemSerial = StockTransferItemSerial::factory()->for($company)->create();

        $stockTransferItemSerialArr = StockTransferItemSerial::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.stock_transfer.stock_transfer_item_serial.edit', $stockTransferItemSerial->ulid), $stockTransferItemSerialArr);

        $api->assertStatus(401);
    }

    public function test_stock_transfer_item_serial_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $stockTransferItemSerial = StockTransferItemSerial::factory()->for($company)->create();

        $stockTransferItemSerialArr = StockTransferItemSerial::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.stock_transfer.stock_transfer_item_serial.edit', $stockTransferItemSerial->ulid), $stockTransferItemSerialArr);

        $api->assertStatus(403);
    }

    public function test_stock_transfer_item_serial_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_stock_transfer_item_serial_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_stock_transfer_item_serial_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $stockTransferItemSerial = StockTransferItemSerial::factory()->for($company)->create();

        $stockTransferItemSerialArr = StockTransferItemSerial::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.stock_transfer.stock_transfer_item_serial.edit', $stockTransferItemSerial->ulid), $stockTransferItemSerialArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('stock_transfer_item_serials', [
            'id' => $stockTransferItemSerial->id,
            'company_id' => $company->id,
            'code' => $stockTransferItemSerialArr['code'],
            'name' => $stockTransferItemSerialArr['name'],
        ]);
    }

    public function test_stock_transfer_item_serial_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_stock_transfer_item_serial_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        StockTransferItemSerial::factory()->for($company)->count(2)->create();

        $stockTransferItemSerials = $company->stockTransferItemSerials()->inRandomOrder()->take(2)->get();
        $stockTransferItemSerial_1 = $stockTransferItemSerials[0];
        $stockTransferItemSerial_2 = $stockTransferItemSerials[1];

        $stockTransferItemSerialArr = StockTransferItemSerial::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $stockTransferItemSerial_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.stock_transfer.stock_transfer_item_serial.edit', $stockTransferItemSerial_2->ulid), $stockTransferItemSerialArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_stock_transfer_item_serial_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        StockTransferItemSerial::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $stockTransferItemSerial_2 = StockTransferItemSerial::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $stockTransferItemSerialArr = StockTransferItemSerial::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.stock_transfer.stock_transfer_item_serial.edit', $stockTransferItemSerial_2->ulid), $stockTransferItemSerialArr);

        $api->assertSuccessful();
    }
}
