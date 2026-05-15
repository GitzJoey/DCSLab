<?php

namespace Tests\Unit\Actions\CustomerAddressActions;

use App\Actions\CustomerAddress\CustomerAddressActions;
use App\Models\Company;
use App\Models\CustomerAddress;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class CustomerAddressActionsEditTest extends ActionsTestCase
{
    private CustomerAddressActions $customerAddressActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerAddressActions = new CustomerAddressActions();
    }

    public function test_customer_address_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(CustomerAddress::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerAddress = $company->customerAddresses()->inRandomOrder()->first();

        $payload = CustomerAddress::factory()->make()->toArray();

        $dto = new \App\DTOs\CustomerAddressUpdateDTO(
            address: $payload['address'],
            city: $payload['city'],
            contact: $payload['contact'],
            isMain: $payload['is_main'],
            remarks: $payload['remarks']
        );

        $result = $this->customerAddressActions->update($customerAddress, $dto);
        $this->assertInstanceOf(CustomerAddress::class, $result);
        $this->assertDatabaseHas('customer_addresses', [
            'id' => $customerAddress->id,
            'company_id' => $customerAddress->company_id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_customer_address_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(CustomerAddress::factory())
            )->create();

        $customerAddress = $user->companies()->inRandomOrder()->first()
            ->customerAddresses()->inRandomOrder()->first();

        $payload = [];

        $dto = new \App\DTOs\CustomerAddressUpdateDTO();

        $this->customerAddressActions->update($customerAddress, $dto);
    }
}
