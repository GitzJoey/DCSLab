<?php

namespace Tests\Feature;

use App\Actions\Customer\CustomerActions;
use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerActionsDeleteTest extends TestCase
{
    use WithFaker;
    
    private CustomerActions $customerActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerActions = new CustomerActions();
    }

    public function test_customer_actions_call_delete_expect_bool()
    {
        $user = User::factory()
                ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(CustomerGroup::factory())
                )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        $customer = Customer::factory()
            ->for($company)->for($customerGroup)
            ->has(CustomerAddress::factory()->for($company)->count(3))
            ->create();

        $result = $this->customerActions->delete($customer);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('customers', [
            'id' => $customer->id,
        ]);
    }
}
