<?php

namespace Tests\Unit\CustomerGroupActions;

use App\Actions\CustomerGroup\CustomerGroupActions;
use App\Models\Company;
use App\Models\CustomerGroup;
use App\Models\User;

use Tests\ActionsTestCase;

class CustomerGroupActionsDeleteTest extends ActionsTestCase
{
    private CustomerGroupActions $customerGroupActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerGroupActions = new CustomerGroupActions();
    }

    public function test_customer_group_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(CustomerGroup::factory())
            )->create();

        $customerGroup = $user->companies()->inRandomOrder()->first()
            ->customerGroups()->inRandomOrder()->first();

        $result = $this->customerGroupActions->delete($customerGroup);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('customer_groups', [
            'id' => $customerGroup->id,
        ]);
    }
}
