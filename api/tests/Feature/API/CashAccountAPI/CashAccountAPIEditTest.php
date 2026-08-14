<?php

namespace Tests\Feature\API\CashAccountAPI;

use App\Enums\UserRolesEnum;
use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class CashAccountAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_cash_account_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $cashAccount = CashAccount::factory()->for($company)->create([
            'branch_id' => $branch->id,
        ]);

        $payload = CashAccount::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.cash_account.edit', $cashAccount->ulid), $payload);

        $api->assertUnauthorized();
    }

    public function test_cash_account_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $cashAccount = CashAccount::factory()->for($company)->create([
            'branch_id' => $branch->id,
        ]);

        $payload = CashAccount::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.cash_account.edit', $cashAccount->ulid), $payload);

        $api->assertForbidden();
    }

    public function test_cash_account_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $cashAccount = CashAccount::factory()->for($company)->create([
            'branch_id' => $branch->id,
        ]);

        $payload = CashAccount::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'name' => '<script>alert("xss")</script>',
        ])->toArray();

        $api = $this->json('POST', route('api.post.cash_account.edit', $cashAccount->ulid), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('cash_accounts', [
            'id' => $cashAccount->id,
            'company_id' => $company->id,
            'name' => 'alert("xss")',
        ]);
    }

    public function test_cash_account_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $cashAccount = CashAccount::factory()->for($company)->create([
            'branch_id' => $branch->id,
        ]);

        $payload = CashAccount::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'name' => '<script>alert("xss")</script>',
        ])->toArray();

        $api = $this->json('POST', route('api.post.cash_account.edit', $cashAccount->ulid), $payload, ['X-Sanitizer-Mode' => 'encode']);

        $api->assertSuccessful();
        $this->assertDatabaseHas('cash_accounts', [
            'id' => $cashAccount->id,
            'company_id' => $company->id,
            'name' => '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;',
        ]);
    }

    public function test_cash_account_api_call_update_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $cashAccount = CashAccount::factory()->for($company)->create([
            'branch_id' => $branch->id,
        ]);

        $payload = CashAccount::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.cash_account.edit', $cashAccount->ulid), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('cash_accounts', [
            'id' => $cashAccount->id,
            'company_id' => $company->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_cash_account_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->first();
        $branch = $company->branches()->inRandomOrder()->first();
        CashAccount::factory()->for($company)->count(2)->create([
            'branch_id' => $branch->id,
        ]);

        $cashAccounts = $company->cashAccounts()->inRandomOrder()->take(2)->get();
        $cashAccount_1 = $cashAccounts[0];
        $cashAccount_2 = $cashAccounts[1];

        $payload = CashAccount::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
            'code' => $cashAccount_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.cash_account.edit', $cashAccount_2->ulid), $payload);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_cash_account_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->has(Company::factory()->setStatusActive()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->whereHas('branches')->inRandomOrder()->get();

        $company_1 = $companies[0];
        $branch_1 = $company_1->branches()->inRandomOrder()->first();
        CashAccount::factory()->for($company_1)->create([
            'code' => 'test1',
            'branch_id' => $branch_1->id,
        ]);

        $company_2 = $companies[1];
        $branch_2 = $company_2->branches()->inRandomOrder()->first();
        $cashAccount_2 = CashAccount::factory()->for($company_2)->create([
            'code' => 'test2',
            'branch_id' => $branch_2->id,
        ]);

        $payload = CashAccount::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.cash_account.edit', $cashAccount_2->ulid), $payload);

        $api->assertSuccessful();
    }
}
