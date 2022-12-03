<?php

namespace Tests\Feature\API;

use Exception;
use App\Models\Role;
use App\Models\User;
use Tests\APITestCase;
use App\Models\Company;
use App\Enums\UserRoles;
use App\Enums\AccountType;
use App\Models\ChartOfAccount;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Foundation\Testing\WithFaker;
use Database\Seeders\ChartOfAccountTableSeeder;

class ChartOfAccountAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->chartOfAccountTableSeeder = new ChartOfAccountTableSeeder();
    }

    /* #region store */
    public function test_chart_of_account_api_call_store_without_using_factory_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        $this->actingAs($user);

        $chartOfAccountArr = ChartOfAccount::factory()->make([
            'company_id' => Hashids::encode($companyId),
        ])->toArray();
        $chartOfAccountArr['code'] =  '[AUTO]';
        $chartOfAccountArr['name'] = 'Assets';
        $chartOfAccountArr['account_type'] = AccountType::BALANCE_SHEET_ROOT->name;

        $api = $this->json('POST', route('api.post.db.finance.chart_of_account.save'), $chartOfAccountArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('chart_of_accounts', [
            'company_id' => Hashids::decode($chartOfAccountArr['company_id'])[0],
            'code' => '01',
            'name' => $chartOfAccountArr['name'],
            'account_type' => AccountType::fromName($chartOfAccountArr['account_type']),
        ]);
    }
    /* #endregion */

    /* #region list */
    public function test_chart_of_account_api_call_list_with_or_without_pagination_expect_paginator_or_collection()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        $this->actingAs($user);

        $this->chartOfAccountTableSeeder->callWith(ChartOfAccountTableSeeder::class, [$companyId]);

        $api = $this->getJson(route('api.get.db.finance.chart_of_account.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => '',
            'paginate' => true,
            'page' => 1,
            'perPage' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }
    /* #endregion */

    /* #region read */
    public function test_chart_of_account_api_call_read_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();

        $this->actingAs($user);

        $this->chartOfAccountTableSeeder->callWith(ChartOfAccountTableSeeder::class, [$company->id]);

        $uuid = $company->chartOfAccounts()->inRandomOrder()->first()->uuid;

        $api = $this->getJson(route('api.get.db.finance.chart_of_account.read', $uuid));

        $api->assertSuccessful();
    }
    /* #endregion */

    /* #region update */
    public function test_chart_of_account_api_call_update_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $this->chartOfAccountTableSeeder->callWith(ChartOfAccountTableSeeder::class, [$companyId]);

        $chartOfAccount = $company->chartOfAccounts()->inRandomOrder()->first();

        $chartOfAccountArr = ChartOfAccount::factory()->make([
            'company_id' => Hashids::encode($companyId),
        ])->toArray();
        $chartOfAccountArr['code'] =  $chartOfAccountArr['code'] . ' new';
        $chartOfAccountArr['name'] = $chartOfAccountArr['name'] . ' new';
        $chartOfAccountArr['remarks'] = $chartOfAccountArr['remarks'] . ' new';

        $api = $this->json('POST', route('api.post.db.finance.chart_of_account.edit', $chartOfAccount->uuid), $chartOfAccountArr);

        $api->assertSuccessful();
        $this->assertDatabaseHas('chart_of_accounts', [
            'id' => $chartOfAccount->id,
            'company_id' => $companyId,
            'code' => $chartOfAccountArr['code'],
            'name' => $chartOfAccountArr['name'],
            'account_type' => $chartOfAccountArr['account_type'],
            'remarks' => $chartOfAccountArr['remarks'],
        ]);
    }
    /* #endregion */

    /* #region delete */
    public function test_chart_of_account_api_call_delete_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $this->actingAs($user);
        
        $company = $user->companies->first();      

        $this->chartOfAccountTableSeeder->callWith(ChartOfAccountTableSeeder::class, [$company->id]);

        $chartOfAccount = $company->chartOfAccounts()->inRandomOrder()->first();

        $api = $this->json('POST', route('api.post.db.finance.chart_of_account.delete', $chartOfAccount->uuid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('chart_of_accounts', [
            'id' => $chartOfAccount->id,
        ]);
    }
    /* #endregion */

    /* #region others */

    /* #endregion */
}