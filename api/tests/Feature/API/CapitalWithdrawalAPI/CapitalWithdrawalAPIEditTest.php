<?php

namespace Tests\Feature\API\CapitalWithdrawalAPI;

use App\Enums\UserRolesEnum;
use App\Models\CapitalWithdrawal;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class CapitalWithdrawalAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_capital_withdrawal_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();
        $capitalWithdrawal = CapitalWithdrawal::factory()->for($company)->create();

        $capitalWithdrawalArr = CapitalWithdrawal::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.capital.capital_withdrawal.edit', $capitalWithdrawal->ulid), $capitalWithdrawalArr);

        $api->assertStatus(401);
    }

    public function test_capital_withdrawal_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $capitalWithdrawal = CapitalWithdrawal::factory()->for($company)->create();

        $capitalWithdrawalArr = CapitalWithdrawal::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.capital.capital_withdrawal.edit', $capitalWithdrawal->ulid), $capitalWithdrawalArr);

        $api->assertStatus(403);
    }

    public function test_capital_withdrawal_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_capital_withdrawal_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_capital_withdrawal_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $capitalWithdrawal = CapitalWithdrawal::factory()->for($company)->create();

        $capitalWithdrawalArr = CapitalWithdrawal::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.capital.capital_withdrawal.edit', $capitalWithdrawal->ulid), $capitalWithdrawalArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('capital_withdrawals', [
            'id' => $capitalWithdrawal->id,
            'company_id' => $company->id,
            'code' => $capitalWithdrawalArr['code'],
            'name' => $capitalWithdrawalArr['name'],
        ]);
    }

    public function test_capital_withdrawal_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_capital_withdrawal_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        CapitalWithdrawal::factory()->for($company)->count(2)->create();

        $capitalWithdrawals = $company->capitalWithdrawals()->inRandomOrder()->take(2)->get();
        $capitalWithdrawal_1 = $capitalWithdrawals[0];
        $capitalWithdrawal_2 = $capitalWithdrawals[1];

        $capitalWithdrawalArr = CapitalWithdrawal::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'code' => $capitalWithdrawal_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.capital.capital_withdrawal.edit', $capitalWithdrawal_2->ulid), $capitalWithdrawalArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_capital_withdrawal_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive())
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->inRandomOrder()->get();

        $company_1 = $companies[0];
        CapitalWithdrawal::factory()->for($company_1)->create([
            'code' => 'test1',
        ]);

        $company_2 = $companies[1];
        $capitalWithdrawal_2 = CapitalWithdrawal::factory()->for($company_2)->create([
            'code' => 'test2',
        ]);

        $capitalWithdrawalArr = CapitalWithdrawal::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.capital.capital_withdrawal.edit', $capitalWithdrawal_2->ulid), $capitalWithdrawalArr);

        $api->assertSuccessful();
    }
}
