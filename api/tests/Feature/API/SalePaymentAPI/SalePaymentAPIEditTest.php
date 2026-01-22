<?php

namespace Tests\Feature\API\SalePaymentAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Role;
use App\Models\SalePayment;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class SalePaymentAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_sale_payment_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $salePayment = SalePayment::factory()->for($company)->create();

        $salePaymentArr = SalePayment::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_payment.edit', $salePayment->ulid), $salePaymentArr);

        $api->assertStatus(401);
    }

    public function test_sale_payment_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $salePayment = SalePayment::factory()->for($company)->create();

        $salePaymentArr = SalePayment::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_payment.edit', $salePayment->ulid), $salePaymentArr);

        $api->assertStatus(403);
    }

    public function test_sale_payment_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_sale_payment_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_sale_payment_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $salePayment = SalePayment::factory()->for($company)->create();

        $salePaymentArr = SalePayment::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_payment.edit', $salePayment->ulid), $salePaymentArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('sale_payments', [
            'id' => $salePayment->id,
            'company_id' => $company->id,
            'code' => $salePaymentArr['code'],
            'name' => $salePaymentArr['name'],
        ]);
    }

    public function test_sale_payment_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_sale_payment_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        SalePayment::factory()->for($company)->count(2)->create();

        $salePayments = $company->salePayments()->inRandomOrder()->take(2)->get();
        $salePayment_1 = $salePayments[0];
        $salePayment_2 = $salePayments[1];

        $salePaymentArr = SalePayment::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $salePayment_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_payment.edit', $salePayment_2->ulid), $salePaymentArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_sale_payment_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        SalePayment::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $salePayment_2 = SalePayment::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $salePaymentArr = SalePayment::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.sales.sale_payment.edit', $salePayment_2->ulid), $salePaymentArr);

        $api->assertSuccessful();
    }
}
