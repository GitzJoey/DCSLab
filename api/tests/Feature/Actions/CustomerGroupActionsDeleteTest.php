<?php

namespace Tests\Feature;

use App\Actions\CustomerGroup\CustomerGroupActions;
use App\Models\User;
use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\CustomerGroupTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerGroupActionsDeleteTest extends TestCase
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

    public function test_customer_group_actions_call_delete_expect_bool()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->customerGroupSeeder->callWith(CustomerGroupTableSeeder::class, [3, $companyId]);

        $customerGroup = $user->companies->first()->customerGroups->first();

        $result = $this->customerGroupActions->delete($customerGroup);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('customer_groups', [
            'id' => $customerGroup->id,
        ]);
    }
}
