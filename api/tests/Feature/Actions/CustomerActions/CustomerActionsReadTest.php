<?php

namespace Tests\Feature;

use App\Actions\Customer\CustomerActions;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerActionsReadTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerActions = new CustomerActions();
    }

    public function test_customer_actions_call_read_expect_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory())
                    )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        Customer::factory()->for($company)->for($customerGroup)->create();

        $customer = $company->customers()->inRandomOrder()->first();

        $result = $this->customerActions->read($customer);

        $this->assertInstanceOf(Customer::class, $result);
    }
}
