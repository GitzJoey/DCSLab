<?php

namespace Tests\Feature\API\CustomerGroupAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\CustomerGroup;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Tests\APITestCase;

class CustomerGroupAPIDeleteTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_customer_group_api_call_delete_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = CustomerGroup::factory()->for($company)->create();

        $api = $this->json('POST', route('api.post.customer_group.delete', $customerGroup->ulid));

        $api->assertStatus(401);
    }

    public function test_customer_group_api_call_delete_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = CustomerGroup::factory()->for($company)->create();

        $api = $this->json('POST', route('api.post.customer_group.delete', $customerGroup->ulid));

        $api->assertStatus(403);
    }

    public function test_customer_group_api_call_delete_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = CustomerGroup::factory()->for($company)->create();

        $api = $this->json('POST', route('api.post.customer_group.delete', $customerGroup->ulid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('customer_groups', [
            'id' => $customerGroup->id,
        ]);
    }

    public function test_customer_group_api_call_delete_of_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $ulid = Str::ulid()->generate();

        $api = $this->json('POST', route('api.post.customer_group.delete', $ulid));

        $api->assertStatus(404);
    }

    public function test_customer_group_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.customer_group.delete', null));

        $api->assertStatus(500);
    }
}
