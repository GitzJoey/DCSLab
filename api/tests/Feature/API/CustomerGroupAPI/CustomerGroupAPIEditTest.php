<?php

namespace Tests\Feature\API\CustomerGroupAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\CustomerGroup;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class CustomerGroupAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_customer_group_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = CustomerGroup::factory()->for($company)->create();

        $customerGroupArr = CustomerGroup::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.customer_group.edit', $customerGroup->ulid), $customerGroupArr);

        $api->assertStatus(401);
    }

    public function test_customer_group_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = CustomerGroup::factory()->for($company)->create();

        $customerGroupArr = CustomerGroup::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.customer_group.edit', $customerGroup->ulid), $customerGroupArr);

        $api->assertStatus(403);
    }

    public function test_customer_group_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_customer_group_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_customer_group_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $customerGroup = CustomerGroup::factory()->for($company)->create();

        $customerGroupArr = CustomerGroup::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.customer_group.edit', $customerGroup->ulid), $customerGroupArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('customer_groups', [
            'id' => $customerGroup->id,
            'company_id' => $company->id,
            'code' => $customerGroupArr['code'],
            'name' => $customerGroupArr['name'],
        ]);
    }

    public function test_customer_group_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_customer_group_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        CustomerGroup::factory()->for($company)->count(2)->create();

        $customerGroups = $company->customerGroups()->inRandomOrder()->take(2)->get();
        $customerGroup_1 = $customerGroups[0];
        $customerGroup_2 = $customerGroups[1];

        $customerGroupArr = CustomerGroup::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $customerGroup_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.customer_group.edit', $customerGroup_2->ulid), $customerGroupArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_customer_group_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        CustomerGroup::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $customerGroup_2 = CustomerGroup::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $customerGroupArr = CustomerGroup::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.customer_group.edit', $customerGroup_2->ulid), $customerGroupArr);

        $api->assertSuccessful();
    }
}
