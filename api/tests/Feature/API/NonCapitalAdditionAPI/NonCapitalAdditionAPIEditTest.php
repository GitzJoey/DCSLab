<?php

namespace Tests\Feature\API\NonCapitalAdditionAPI;

use App\Enums\UserRolesEnum;
use App\Models\Company;
use App\Models\NonCapitalAddition;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class NonCapitalAdditionAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_non_capital_addition_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $nonCapitalAddition = NonCapitalAddition::factory()->for($company)->create();

        $nonCapitalAdditionArr = NonCapitalAddition::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.capital.non_capital_addition.edit', $nonCapitalAddition->ulid), $nonCapitalAdditionArr);

        $api->assertStatus(401);
    }

    public function test_non_capital_addition_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $nonCapitalAddition = NonCapitalAddition::factory()->for($company)->create();

        $nonCapitalAdditionArr = NonCapitalAddition::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.capital.non_capital_addition.edit', $nonCapitalAddition->ulid), $nonCapitalAdditionArr);

        $api->assertStatus(403);
    }

    public function test_non_capital_addition_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_non_capital_addition_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_non_capital_addition_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $nonCapitalAddition = NonCapitalAddition::factory()->for($company)->create();

        $nonCapitalAdditionArr = NonCapitalAddition::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.capital.non_capital_addition.edit', $nonCapitalAddition->ulid), $nonCapitalAdditionArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('non_capital_additions', [
            'id' => $nonCapitalAddition->id,
            'company_id' => $company->id,
            'code' => $nonCapitalAdditionArr['code'],
            'name' => $nonCapitalAdditionArr['name'],
        ]);
    }

    public function test_non_capital_addition_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_non_capital_addition_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        NonCapitalAddition::factory()->for($company)->count(2)->create();

        $nonCapitalAdditions = $company->nonCapitalAdditions()->inRandomOrder()->take(2)->get();
        $nonCapitalAddition_1 = $nonCapitalAdditions[0];
        $nonCapitalAddition_2 = $nonCapitalAdditions[1];

        $nonCapitalAdditionArr = NonCapitalAddition::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $nonCapitalAddition_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.capital.non_capital_addition.edit', $nonCapitalAddition_2->ulid), $nonCapitalAdditionArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_non_capital_addition_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        NonCapitalAddition::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $nonCapitalAddition_2 = NonCapitalAddition::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $nonCapitalAdditionArr = NonCapitalAddition::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.capital.non_capital_addition.edit', $nonCapitalAddition_2->ulid), $nonCapitalAdditionArr);

        $api->assertSuccessful();
    }
}
