<?php

namespace Tests\Unit\Actions\CustomerAddressActions;

use App\Actions\CustomerAddress\CustomerAddressActions;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\User;
use Tests\ActionsTestCase;

class CustomerAddressActionsDeleteTest extends ActionsTestCase
{
    private CustomerAddressActions $customerAddressActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerAddressActions = new CustomerAddressActions();
    }

    public function test_customer_address_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Customer::factory(), 'customers'))
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customer = $company->customers()->inRandomOrder()->first();

        $customerAddress = CustomerAddress::factory()
            ->for($customer, 'customer')
            ->create();
        $result = $this->customerAddressActions->delete($customerAddress);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('customer_addresses', [
            'id' => $customerAddress->id,
        ]);
    }
}
