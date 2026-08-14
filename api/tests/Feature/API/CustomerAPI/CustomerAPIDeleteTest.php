<?php

namespace Tests\Feature\API\CustomerAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Tests\APITestCase;

class CustomerAPIDeleteTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_customer_api_call_delete_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customer = Customer::factory()->for($company)->create();

        $api = $this->json('POST', route('api.post.db.customer.customer.delete', $customer->ulid));

        $api->assertUnauthorized();
    }

    public function test_customer_api_call_delete_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $customer = Customer::factory()->for($company)->create();

        $api = $this->json('POST', route('api.post.db.customer.customer.delete', $customer->ulid));

        $api->assertForbidden();
    }

    public function test_customer_api_call_delete_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $customer = Customer::factory()->for($company)->create();

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
