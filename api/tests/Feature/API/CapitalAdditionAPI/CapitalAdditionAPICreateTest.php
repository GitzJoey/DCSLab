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
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class CapitalAdditionAPICreateTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_capital_addition_api_call_store_without_authorization_expect_unauthorized_message()
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
        $investor = Investor::factory()->for($company)->create();

        $capitalAdditionArr = CapitalAddition::factory()->make()->toArray();
        $capitalAdditionArr['company_id'] = Hashids::encode($company->id);
        $capitalAdditionArr['branch_id'] = Hashids::encode($branch->id);
        $capitalAdditionArr['investor_id'] = Hashids::encode($investor->id);
        $capitalAdditionArr['cash_account_id'] = Hashids::encode($cashAccount->id);

        $api = $this->json('POST', route('api.post.db.capital.capital_addition.save'), $capitalAdditionArr);

        $api->assertUnauthorized();
    }

    public function test_capital_addition_api_call_store_without_access_right_expect_unauthorized_message()
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
        $investor = Investor::factory()->for($company)->create();

        $capitalAdditionArr = CapitalAddition::factory()->make()->toArray();
        $capitalAdditionArr['company_id'] = Hashids::encode($company->id);
        $capitalAdditionArr['branch_id'] = Hashids::encode($branch->id);
        $capitalAdditionArr['investor_id'] = Hashids::encode($investor->id);
        $capitalAdditionArr['cash_account_id'] = Hashids::encode($cashAccount->id);

        $api = $this->json('POST', route('api.post.db.capital.capital_addition.save'), $capitalAdditionArr);

        $api->assertForbidden();
    }

    public function test_capital_addition_api_call_store_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_capital_addition_api_call_store_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_capital_addition_api_call_store_expect_successful()
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
        $investor = Investor::factory()->for($company)->create();

        $capitalAdditionArr = CapitalAddition::factory()->make()->toArray();
        $capitalAdditionArr['company_id'] = Hashids::encode($company->id);
        $capitalAdditionArr['branch_id'] = Hashids::encode($branch->id);
        $capitalAdditionArr['investor_id'] = Hashids::encode($investor->id);
        $capitalAdditionArr['cash_account_id'] = Hashids::encode($cashAccount->id);

        $api = $this->json('POST', route('api.post.db.capital.capital_addition.save'), $capitalAdditionArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('capital_additions', [
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'code' => $capitalAdditionArr['code'],
            'date' => $capitalAdditionArr['date'],
            'investor_id' => $investor->id,
            'cash_account_id' => $cashAccount->id,
        ]);
    }

    public function test_capital_addition_api_call_store_with_nonexistance_branch_id_expect_failed()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    public function test_capital_addition_api_call_store_with_existing_code_in_same_company_expect_failed()
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
        $investor = Investor::factory()->for($company)->create();

        CapitalAddition::factory()->for($company)->create([
            'branch_id' => $branch->id,
            'investor_id' => $investor->id,
            'cash_account_id' => $cashAccount->id,
            'code' => 'test1',
        ]);

        $capitalAdditionArr = CapitalAddition::factory()->make()->toArray();
        $capitalAdditionArr['company_id'] = Hashids::encode($company->id);
        $capitalAdditionArr['branch_id'] = Hashids::encode($branch->id);
        $capitalAdditionArr['investor_id'] = Hashids::encode($investor->id);
        $capitalAdditionArr['cash_account_id'] = Hashids::encode($cashAccount->id);
        $capitalAdditionArr['code'] = 'test1';

        $api = $this->json('POST', route('api.post.db.capital.capital_addition.save'), $capitalAdditionArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_capital_addition_api_call_store_with_existing_code_in_different_company_expect_successful()
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
        $cashAccount_1 = CashAccount::factory()->for($company_1)->create([
            'branch_id' => $branch_1->id,
        ]);
        $investor_1 = Investor::factory()->for($company_1)->create();
        CapitalAddition::factory()->for($company_1)->create([
            'branch_id' => $branch_1->id,
            'investor_id' => $investor_1->id,
            'cash_account_id' => $cashAccount_1->id,
            'code' => 'test1',
        ]);

        $branch_2 = $company_2->branches()->inRandomOrder()->first();
        $cashAccount_2 = CashAccount::factory()->for($company_2)->create([
            'branch_id' => $branch_2->id,
        ]);
        $investor_2 = Investor::factory()->for($company_2)->create();
        $capitalAdditionArr = CapitalAddition::factory()->make()->toArray();
        $capitalAdditionArr['company_id'] = Hashids::encode($company_2->id);
        $capitalAdditionArr['branch_id'] = Hashids::encode($branch_2->id);
        $capitalAdditionArr['investor_id'] = Hashids::encode($investor_2->id);
        $capitalAdditionArr['cash_account_id'] = Hashids::encode($cashAccount_2->id);
        $capitalAdditionArr['code'] = 'test1';

        $api = $this->json('POST', route('api.post.db.capital.capital_addition.save'), $capitalAdditionArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('capital_additions', [
            'company_id' => $company_2->id,
            'branch_id' => $branch_2->id,
            'code' => $capitalAdditionArr['code'],
            'date' => $capitalAdditionArr['date'],
            'investor_id' => $investor_2->id,
            'cash_account_id' => $cashAccount_2->id,
        ]);
    }

    public function test_capital_addition_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRolesEnum::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $this->actingAs($user);

        $capitalAdditionArr = [];

        $api = $this->json('POST', route('api.post.db.capital.capital_addition.save'), $capitalAdditionArr);

        $api->assertJsonValidationErrors(['company_id', 'branch_id', 'code', 'date', 'investor_id', 'cash_account_id', 'amount']);
    }
}
