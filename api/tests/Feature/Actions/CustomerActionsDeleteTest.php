<?php

namespace Tests\Feature;

use App\Actions\Customer\CustomerActions;
use App\Actions\RandomGenerator;
use App\Models\User;
use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\CustomerGroupTableSeeder;
use Database\Seeders\CustomerTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerActionsDeleteTest extends TestCase
{
    use WithFaker;

    private $customerActions;

    private $customerAddressActions;

    private $companySeeder;

    private $customerGroupSeeder;

    private $customerSeeder;

    private $randomGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerActions = app(CustomerActions::class);
        $this->companySeeder = new CompanyTableSeeder();
        $this->customerGroupSeeder = new CustomerGroupTableSeeder();
        $this->customerSeeder = new CustomerTableSeeder();

        $this->randomGenerator = new RandomGenerator();
    }

    public function test_customer_actions_call_delete_expect_bool()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->customerGroupSeeder->callWith(CustomerGroupTableSeeder::class, [5, $companyId]);
        $this->customerSeeder->callWith(CustomerTableSeeder::class, [10, $companyId]);

        $customer = $company->customers()->inRandomOrder()->first();

        $result = $this->customerActions->delete($customer);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('customers', [
            'id' => $customer->id,
        ]);
    }
}
