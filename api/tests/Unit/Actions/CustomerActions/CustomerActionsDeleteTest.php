<?php

namespace Tests\Unit\Actions\CustomerActions;

use App\Actions\Customer\CustomerActions;
use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Tests\ActionsTestCase;

class CustomerActionsDeleteTest extends ActionsTestCase
{
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
                ->has(Customer::factory())
            )->create();

        $customer = $user->companies()->inRandomOrder()->first()
            ->customers()->inRandomOrder()->first();
        $result = $this->customerActions->delete($customer);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('customers', [
            'id' => $customer->id,
        ]);
    }
}
