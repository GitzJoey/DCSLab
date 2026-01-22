<?php

namespace Tests\Feature\API\SaleReceiptAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Role;
use App\Models\SaleReceipt;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class SaleReceiptAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_sale_receipt_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $saleReceipt = SaleReceipt::factory()->for($company)->create();

        $saleReceiptArr = SaleReceipt::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_receipt.edit', $saleReceipt->ulid), $saleReceiptArr);

        $api->assertStatus(401);
    }

    public function test_sale_receipt_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $saleReceipt = SaleReceipt::factory()->for($company)->create();

        $saleReceiptArr = SaleReceipt::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_receipt.edit', $saleReceipt->ulid), $saleReceiptArr);

        $api->assertStatus(403);
    }

    public function test_sale_receipt_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_sale_receipt_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_sale_receipt_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $saleReceipt = SaleReceipt::factory()->for($company)->create();

        $saleReceiptArr = SaleReceipt::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_receipt.edit', $saleReceipt->ulid), $saleReceiptArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('sale_receipts', [
            'id' => $saleReceipt->id,
            'company_id' => $company->id,
            'code' => $saleReceiptArr['code'],
            'name' => $saleReceiptArr['name'],
        ]);
    }

    public function test_sale_receipt_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_sale_receipt_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        SaleReceipt::factory()->for($company)->count(2)->create();

        $saleReceipts = $company->saleReceipts()->inRandomOrder()->take(2)->get();
        $saleReceipt_1 = $saleReceipts[0];
        $saleReceipt_2 = $saleReceipts[1];

        $saleReceiptArr = SaleReceipt::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $saleReceipt_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_receipt.edit', $saleReceipt_2->ulid), $saleReceiptArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_sale_receipt_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        SaleReceipt::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $saleReceipt_2 = SaleReceipt::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $saleReceiptArr = SaleReceipt::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_receipt.edit', $saleReceipt_2->ulid), $saleReceiptArr);

        $api->assertSuccessful();
    }
}
