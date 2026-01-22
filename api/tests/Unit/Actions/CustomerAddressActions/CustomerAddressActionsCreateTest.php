<?php

namespace Tests\Unit\Actions\CustomerAddressActions;

use App\Actions\CustomerAddress\CustomerAddressActions;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\User;
use Exception;
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

        $customerAddressArr = CustomerAddress::factory()->for($company)
            ->make([
                'customer_id' => $customer->id,
            ])->toArray();

        $result = $this->customerAddressActions->create($customerAddressArr);

        $this->assertDatabaseHas('customer_addresses', [
            'id' => $result->id,
            'company_id' => $customerAddressArr['company_id'],
            'customer_id' => $customerAddressArr['customer_id'],
            'address' => $customerAddressArr['address'],
            'city' => $customerAddressArr['city'],
            'contact' => $customerAddressArr['contact'],
            'is_main' => $customerAddressArr['is_main'],
            'remarks' => $customerAddressArr['remarks'],
        ]);
    }

    public function test_customer_address_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->customerAddressActions->create([]);
    }
}
