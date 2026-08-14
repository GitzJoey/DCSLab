<?php

namespace Tests\Unit\Actions\CustomerAddressActions;

use App\Actions\CustomerAddress\CustomerAddressActions;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\User;
use Tests\ActionsTestCase;

class CustomerAddressActionsCreateTest extends ActionsTestCase
{
    private CustomerAddressActions $customerAddressActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerAddressActions = new CustomerAddressActions();
    }

    public function test_customer_address_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $customer = Customer::factory()->for($company)->create();

        $payload = CustomerAddress::factory()->for($company)
            ->make([
                'customer_id' => $customer->id,
            ])->toArray();

        $dto = new \App\DTOs\CustomerAddressCreateDTO(
            companyId: $payload['company_id'],
            customerId: $payload['customer_id'],
            address: $payload['address'],
            city: $payload['city'],
            contact: $payload['contact'],
            isMain: $payload['is_main'],
            remarks: $payload['remarks']
        );

        $result = $this->customerAddressActions->create($dto);
        $this->assertDatabaseHas('customer_addresses', [
            'id' => $result->id,
            'company_id' => $payload['company_id'],
            'customer_id' => $payload['customer_id'],
            'address' => $payload['address'],
            'city' => $payload['city'],
            'contact' => $payload['contact'],
            'is_main' => $payload['is_main'],
            'remarks' => $payload['remarks'],
        ]);
    }

    public function test_customer_address_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $dto = new \App\DTOs\CustomerAddressCreateDTO();

        $this->customerAddressActions->create($dto);
    }
}
