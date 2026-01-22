<?php

namespace Tests\Feature\API\SaleReceiptProductUnitSerialAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Role;
use App\Models\SaleReceiptProductUnitSerial;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class SaleReceiptProductUnitSerialAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_sale_receipt_product_unit_serial_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $saleReceiptProductUnitSerial = SaleReceiptProductUnitSerial::factory()->for($company)->create();

        $saleReceiptProductUnitSerialArr = SaleReceiptProductUnitSerial::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_receipt_product_unit_serial.edit', $saleReceiptProductUnitSerial->ulid), $saleReceiptProductUnitSerialArr);

        $api->assertStatus(401);
    }

    public function test_sale_receipt_product_unit_serial_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $saleReceiptProductUnitSerial = SaleReceiptProductUnitSerial::factory()->for($company)->create();

        $saleReceiptProductUnitSerialArr = SaleReceiptProductUnitSerial::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_receipt_product_unit_serial.edit', $saleReceiptProductUnitSerial->ulid), $saleReceiptProductUnitSerialArr);

        $api->assertStatus(403);
    }

    public function test_sale_receipt_product_unit_serial_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_sale_receipt_product_unit_serial_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_sale_receipt_product_unit_serial_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $saleReceiptProductUnitSerial = SaleReceiptProductUnitSerial::factory()->for($company)->create();

        $saleReceiptProductUnitSerialArr = SaleReceiptProductUnitSerial::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_receipt_product_unit_serial.edit', $saleReceiptProductUnitSerial->ulid), $saleReceiptProductUnitSerialArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('sale_receipt_product_unit_serials', [
            'id' => $saleReceiptProductUnitSerial->id,
            'company_id' => $company->id,
            'code' => $saleReceiptProductUnitSerialArr['code'],
            'name' => $saleReceiptProductUnitSerialArr['name'],
        ]);
    }

    public function test_sale_receipt_product_unit_serial_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_sale_receipt_product_unit_serial_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        SaleReceiptProductUnitSerial::factory()->for($company)->count(2)->create();

        $saleReceiptProductUnitSerials = $company->saleReceiptProductUnitSerials()->inRandomOrder()->take(2)->get();
        $saleReceiptProductUnitSerial_1 = $saleReceiptProductUnitSerials[0];
        $saleReceiptProductUnitSerial_2 = $saleReceiptProductUnitSerials[1];

        $saleReceiptProductUnitSerialArr = SaleReceiptProductUnitSerial::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $saleReceiptProductUnitSerial_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_receipt_product_unit_serial.edit', $saleReceiptProductUnitSerial_2->ulid), $saleReceiptProductUnitSerialArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_sale_receipt_product_unit_serial_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        SaleReceiptProductUnitSerial::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $saleReceiptProductUnitSerial_2 = SaleReceiptProductUnitSerial::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $saleReceiptProductUnitSerialArr = SaleReceiptProductUnitSerial::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_receipt_product_unit_serial.edit', $saleReceiptProductUnitSerial_2->ulid), $saleReceiptProductUnitSerialArr);

        $api->assertSuccessful();
    }
}
