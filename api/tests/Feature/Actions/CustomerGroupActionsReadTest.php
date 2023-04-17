<?php

namespace Tests\Feature;

use App\Actions\CustomerGroup\CustomerGroupActions;
use App\Models\CustomerGroup;
use App\Models\User;
use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\CustomerGroupTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerGroupActionsReadTest extends TestCase
{
    use WithFaker;

    private $customerGroupActions;

    private $companySeeder;

    private $customerGroupSeeder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerGroupActions = app(CustomerGroupActions::class);

        $this->companySeeder = new CompanyTableSeeder();
        $this->customerGroupSeeder = new CustomerGroupTableSeeder();
    }

    public function test_customer_group_actions_call_read_expect_object()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->customerGroupSeeder->callWith(CustomerGroupTableSeeder::class, [3, $companyId]);

        $customerGroup = $user->companies->first()->customerGroups()->inRandomOrder()->first();

        $result = $this->customerGroupActions->read($customerGroup);

        $this->assertInstanceOf(CustomerGroup::class, $result);
    }
}
