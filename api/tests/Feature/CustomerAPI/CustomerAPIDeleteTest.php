<?php

namespace Tests\Feature\CustomerAPI;

use Exception;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use App\Models\Company;
use App\Enums\UserRoles;
use App\Models\Customer;
use Illuminate\Support\Str;
use App\Models\CustomerGroup;
use App\Models\CustomerAddress;

class CustomerAPIDeleteTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_customer_api_call_delete_expect_successful()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(CustomerGroup::factory()->count(3))
                    )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = $company->customerGroups()->inRandomOrder()->first();

        $customer = Customer::factory()->for($company)->for($customerGroup);
        for ($i = 0; $i < 3; $i++) {
            $customer = $customer->has(CustomerAddress::factory()->for($company));
        }
        $customer = $customer->create();

        $api = $this->json('POST', route('api.post.db.customer.customer.delete', $customer->ulid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_customer_api_call_delete_of_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()->create();

        $this->actingAs($user);
        
        $ulid = Str::ulid()->generate();

        $api = $this->json('POST', route('api.post.db.customer.customer.delete', $ulid));

        $api->assertStatus(404);
    }

    public function test_customer_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->json('POST', route('api.post.db.customer.customer.delete', null));
    }
}
