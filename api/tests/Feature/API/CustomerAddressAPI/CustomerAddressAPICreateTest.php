<?php

namespace Tests\Feature\API\CustomerAddressAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\CustomerAddress;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class CustomerAddressAPICreateTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_customer_address_api_call_store_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $customerAddressArr = CustomerAddress::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.customer.customer_address.save'), $customerAddressArr);

        $api->assertUnauthorized();
    }

    public function test_customer_address_api_call_store_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $customerAddressArr = CustomerAddress::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.customer.customer_address.save'), $customerAddressArr);

        $api->assertForbidden();
    }

    public function test_customer_address_api_call_store_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_customer_address_api_call_store_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_customer_address_api_call_store_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $customerAddressArr = CustomerAddress::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.customer.customer_address.save'), $customerAddressArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('customer_addresses', [
            'company_id' => $company->id,
            'code' => $customerAddressArr['code'],
            'name' => $customerAddressArr['name'],
        ]);
    }

    public function test_customer_address_api_call_store_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_customer_address_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        CustomerAddress::factory()->for($company)->create([
            'code' => 'test1',
        ]);

        $customerAddressArr = CustomerAddress::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.customer.customer_address.save'), $customerAddressArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_customer_address_api_call_store_with_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->take(2)->get();

        $company_1 = $companies[0];

        $company_2 = $companies[1];

        CustomerAddress::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $customerAddressArr = CustomerAddress::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.customer.customer_address.save'), $customerAddressArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('customer_addresses', [
            'company_id' => $company_2->id,
            'code' => $customerAddressArr['code'],
            'name' => $customerAddressArr['name'],
        ]);
    }

    public function test_customer_address_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $customerAddressArr = [];

        $api = $this->json('POST', route('api.post.db.customer.customer_address.save'), $customerAddressArr);

        $api->assertJsonValidationErrors(['company_id', 'code', 'name']);
    }
}
