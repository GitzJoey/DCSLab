<?php

namespace Tests\Unit\Actions\CustomerGroupActions;

use App\Actions\CustomerGroup\CustomerGroupActions;
use App\Models\Company;
use App\Models\CustomerGroup;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class CustomerGroupActionsCreateTest extends ActionsTestCase
{
    private CustomerGroupActions $customerGroupActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerGroupActions = new CustomerGroupActions();
    }

    public function test_customer_group_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $customerGroupArr = CustomerGroup::factory()->for($company)
            ->make()->toArray();

        $result = $this->customerGroupActions->create($customerGroupArr);

        $this->assertDatabaseHas('customer_groups', [
            'id' => $result->id,
            'company_id' => $customerGroupArr['company_id'],
            'code' => $customerGroupArr['code'],
            'name' => $customerGroupArr['name'],
        ]);
    }

    public function test_customer_group_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->customerGroupActions->create([]);
    }
}
