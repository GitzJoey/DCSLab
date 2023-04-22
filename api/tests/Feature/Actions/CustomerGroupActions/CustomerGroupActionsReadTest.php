<?php

namespace Tests\Feature;

use App\Actions\CustomerGroup\CustomerGroupActions;
use App\Models\Company;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerGroupActionsReadTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerGroupActions = new CustomerGroupActions();
    }

    public function test_customer_group_actions_call_read_expect_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(CustomerGroup::factory())
                    )->create();

        $customerGroup = $user->companies()->inRandomOrder()->first()
                            ->customerGroups()->inRandomOrder()->first();

        $result = $this->customerGroupActions->read($customerGroup);

        $this->assertInstanceOf(CustomerGroup::class, $result);
    }
}
