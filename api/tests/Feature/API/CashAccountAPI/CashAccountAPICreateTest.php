<?php

namespace Tests\Feature\API\CashAccountAPI;

use App\Enums\UserRolesEnum;
use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class CashAccountAPICreateTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_cash_account_api_call_store_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();

        $payload = CashAccount::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.cash_account.save'), $payload);

        $api->assertUnauthorized();
    }

    public function test_cash_account_api_call_store_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();

        $payload = CashAccount::factory()->make([
            'company_id' => Hashids::encode($company->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.cash_account.save'), $payload);

        $api->assertForbidden();
    }

    public function test_cash_account_api_call_store_with_script_tags_in_payload_expect_stripped()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        $payload = CashAccount::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
            'name' => '<script>alert("xss")</script>',
        ])->toArray();

        $api = $this->json('POST', route('api.post.cash_account.save'), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('cash_accounts', [
            'company_id' => $company->id,
            'name' => 'alert("xss")',
        ]);
    }

    public function test_cash_account_api_call_store_with_script_tags_in_payload_expect_encoded()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        $payload = CashAccount::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
            'name' => '<script>alert("xss")</script>',
        ])->toArray();

        $api = $this->json('POST', route('api.post.cash_account.save'), $payload, ['X-Sanitizer-Mode' => 'encode']);

        $api->assertSuccessful();
        $this->assertDatabaseHas('cash_accounts', [
            'company_id' => $company->id,
            'name' => '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;',
        ]);
    }

    public function test_cash_account_api_call_store_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        $payload = CashAccount::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
        ])->toArray();

        $api = $this->json('POST', route('api.post.cash_account.save'), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('cash_accounts', [
            'company_id' => $company->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_cash_account_api_call_store_with_auto_code_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        $payload = CashAccount::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
            'code' => Config::get('dcslab.KEYWORDS.AUTO'),
        ])->toArray();

        $api = $this->json('POST', route('api.post.cash_account.save'), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('cash_accounts', [
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'name' => $payload['name'],
        ]);
    }

    public function test_cash_account_api_call_store_with_nonexistance_branch_id_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();

        $payload = CashAccount::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($company->id + 999), // Invalid Branch ID
        ])->toArray();

        $api = $this->json('POST', route('api.post.cash_account.save'), $payload);

        $api->assertStatus(422);
        $api->assertJsonValidationErrors(['branch_id']);
    }

    public function test_cash_account_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory())
            )->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        CashAccount::factory()->for($company)->create([
            'code' => 'test1',
            'branch_id' => $branch->id,
        ]);

        $payload = CashAccount::factory()->make([
            'company_id' => Hashids::encode($company->id),
            'branch_id' => Hashids::encode($branch->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.cash_account.save'), $payload);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_cash_account_api_call_store_with_existing_code_in_different_company_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->has(Company::factory()->setStatusActive()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->whereHas('branches')->inRandomOrder()->take(2)->get();

        $company_1 = $companies[0];

        $company_2 = $companies[1];

        $branch_1 = $company_1->branches()->inRandomOrder()->first();

        CashAccount::factory()->for($company_1)->create([
            'code' => 'test1',
            'branch_id' => $branch_1->id,
        ]);

        $branch_2 = $company_2->branches()->inRandomOrder()->first();

        $payload = CashAccount::factory()->make([
            'company_id' => Hashids::encode($company_2->id),
            'branch_id' => Hashids::encode($branch_2->id),
            'code' => 'test1',
        ])->toArray();

        $api = $this->json('POST', route('api.post.cash_account.save'), $payload);

        $api->assertSuccessful();
        $this->assertDatabaseHas('cash_accounts', [
            'company_id' => $company_2->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_cash_account_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $payload = [];

        $api = $this->json('POST', route('api.post.cash_account.save'), $payload);

        $api->assertJsonValidationErrors(['company_id', 'code', 'name', 'is_bank', 'is_active']);
    }
}
