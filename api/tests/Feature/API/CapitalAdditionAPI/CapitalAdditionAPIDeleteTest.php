<?php

namespace Tests\Feature\API\CapitalAdditionAPI;

use App\Enums\UserRolesEnum;
use App\Models\Branch;
use App\Models\CapitalAddition;
use App\Models\CashAccount;
use App\Models\Company;
use App\Models\Investor;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Str;
use Tests\APITestCase;

class CapitalAdditionAPIDeleteTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_capital_addition_api_call_delete_without_authorization_expect_unauthorized_message()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
        $investor = Investor::factory()->for($company)->create();
        $capitalAddition = CapitalAddition::factory()->for($company)->create([
            'branch_id' => $branch->id,
            'investor_id' => $investor->id,
            'cash_account_id' => $cashAccount->id,
        ]);

        $api = $this->json('POST', route('api.post.db.capital.capital_addition.delete', $capitalAddition->ulid));

        $api->assertUnauthorized();
    }

    public function test_capital_addition_api_call_delete_without_access_right_expect_unauthorized_message()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
        $investor = Investor::factory()->for($company)->create();
        $capitalAddition = CapitalAddition::factory()->for($company)->create([
            'branch_id' => $branch->id,
            'investor_id' => $investor->id,
            'cash_account_id' => $cashAccount->id,
        ]);

        $api = $this->json('POST', route('api.post.db.capital.capital_addition.delete', $capitalAddition->ulid));

        $api->assertForbidden();
    }

    public function test_capital_addition_api_call_delete_expect_successful()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()->has(Branch::factory()))
            ->create();

        $this->actingAs($user);

        $company = $user->companies()->whereHas('branches')->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();
        $cashAccount = CashAccount::factory()->for($company)->create(['branch_id' => $branch->id]);
        $investor = Investor::factory()->for($company)->create();
        $capitalAddition = CapitalAddition::factory()->for($company)->create([
            'branch_id' => $branch->id,
            'investor_id' => $investor->id,
            'cash_account_id' => $cashAccount->id,
        ]);

        $api = $this->json('POST', route('api.post.db.capital.capital_addition.delete', $capitalAddition->ulid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('capital_additions', [
            'id' => $capitalAddition->id,
        ]);
    }

    public function test_capital_addition_api_call_delete_of_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $ulid = Str::ulid()->generate();

        $api = $this->json('POST', route('api.post.db.capital.capital_addition.delete', $ulid));

        $api->assertStatus(404);
    }

    public function test_capital_addition_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        $user = User::factory()->create();

        $this->actingAs($user);
        $this->json('POST', route('api.post.db.capital.capital_addition.delete', null));
    }
}
