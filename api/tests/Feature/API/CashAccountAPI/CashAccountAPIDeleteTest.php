<?php

namespace Tests\Feature\API\CashAccountAPI;

use App\Enums\UserRolesEnum;
use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Tests\APITestCase;

class CashAccountAPIDeleteTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_cash_account_api_call_delete_without_authorization_expect_unauthorized_message()
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

        $api = $this->json('POST', route('api.post.cash_account.delete', $cashAccount->ulid));

        $api->assertUnauthorized();
    }

    public function test_cash_account_api_call_delete_without_access_right_expect_unauthorized_message()
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

        $api = $this->json('POST', route('api.post.cash_account.delete', $cashAccount->ulid));

        $api->assertForbidden();
    }

    public function test_cash_account_api_call_delete_expect_successful()
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

        $api = $this->json('POST', route('api.post.cash_account.delete', $cashAccount->ulid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('cash_accounts', [
            'id' => $cashAccount->id,
        ]);
    }

    public function test_cash_account_api_call_delete_of_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $ulid = Str::ulid()->generate();

        $api = $this->json('POST', route('api.post.cash_account.delete', $ulid));

        $api->assertStatus(404);
    }

    public function test_cash_account_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->json('POST', route('api.post.cash_account.delete', null));
    }
}
