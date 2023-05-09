<?php

namespace Tests\Feature\EmployeeAPI;

use Exception;
use App\Models\Role;
use App\Models\User;
use App\Models\Branch;
use Tests\APITestCase;
use App\Models\Company;
use App\Models\Profile;
use App\Models\Setting;
use App\Enums\UserRoles;
use App\Models\Employee;
use App\Models\EmployeeAccess;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Foundation\Testing\WithFaker;

class EmployeeAPIReadTest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_employee_api_call_read_any_with_or_without_pagination_expect_paginator_or_collection()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                        ->has(Branch::factory()->count(2)))
                    ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        for ($i = 0; $i < 3; $i++) {
            $employee = Employee::factory()
                            ->for($company)
                            ->for(
                                User::factory()
                                    ->has(Profile::factory())
                                    ->hasAttached(Role::where('name', '=', UserRoles::USER->value)->first())
                                    ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
                                    ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
                                    ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
                            );

            $branches = $company->branches();
            $branchCount = $branches->count();

            if ($branchCount > 0) {
                $accessCount = random_int(1, $branchCount);
                $employee_branchs = $branches->inRandomOrder()->take($accessCount)->get();

                for ($j = 0; $j < $accessCount; $j++) {
                    $employee = $employee->has(EmployeeAccess::factory()->for($company)->for($employee_branchs[$j]));
                }
            }

            $employee->create();
        }

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.employee.read_any', [
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'paginate' => true,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);

        $api = $this->getJson(route('api.get.db.company.employee.read_any', [
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'paginate' => false,
            'page' => 1,
            'per_page' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
    }

    public function test_employee_api_call_read_any_with_search_expect_filtered_results()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                        ->has(Branch::factory()->count(2)))
                    ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        for ($i = 0; $i < 3; $i++) {
            $employee = Employee::factory()
                        ->for($company)
                        ->for(
                            User::factory()
                                ->has(Profile::factory())
                                ->hasAttached(Role::where('name', '=', UserRoles::USER->value)->first())
                                ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
                                ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
                                ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
                        );

            $branches = $company->branches();
            $branchCount = $branches->count();

            if ($branchCount > 0) {
                $accessCount = random_int(1, $branchCount);
                $employee_branchs = $branches->inRandomOrder()->take($accessCount)->get();

                for ($j = 0; $j < $accessCount; $j++) {
                    $employee = $employee->has(EmployeeAccess::factory()->for($company)->for($employee_branchs[$j]));
                }
            }

            $employee->create();
        }

        for ($i = 0; $i < 2; $i++) {
            $employee = Employee::factory()
                        ->for($company)
                        ->for(
                            User::factory()->setName('testing'.$i)
                                ->has(Profile::factory())
                                ->hasAttached(Role::where('name', '=', UserRoles::USER->value)->first())
                                ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
                                ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
                                ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
                        );

            $branches = $company->branches();
            $branchCount = $branches->count();

            if ($branchCount > 0) {
                $accessCount = random_int(1, $branchCount);
                $employee_branchs = $branches->inRandomOrder()->take($accessCount)->get();

                for ($j = 0; $j < $accessCount; $j++) {
                    $employee = $employee->has(EmployeeAccess::factory()->for($company)->for($employee_branchs[$j]));
                }
            }

            $employee->create();
        }

        $api = $this->getJson(route('api.get.db.company.employee.read_any', [
            'company_id' => Hashids::encode($company->id),
            'search' => 'testing',
            'paginate' => true,
            'page' => 1,
            'per_page' => 3,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);

        $api->assertJsonFragment([
            'total' => 2,
        ]);
    }

    public function test_employee_api_call_read_any_without_search_querystring_expect_failed()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                        ->has(Branch::factory()->count(2)))
                    ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.db.company.employee.read_any', [
            'company_id' => Hashids::encode($company->id),
        ]));

        $api->assertStatus(422);
    }

    public function test_employee_api_call_read_any_with_special_char_in_search_expect_results()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.db.company.employee.read_any', [
            'company_id' => Hashids::encode($company->id),
            'search' => " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~",
            'paginate' => true,
            'page' => 1,
            'per_page' => 10,
            'refresh' => false,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }

    public function test_employee_api_call_read_any_with_negative_value_in_parameters_expect_results()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        $api = $this->getJson(route('api.get.db.company.employee.read_any', [
            'company_id' => Hashids::encode($company->id),
            'search' => '',
            'paginate' => true,
            'page' => -1,
            'per_page' => -10,
            'refresh' => false,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }

    public function test_employee_api_call_read_expect_successful()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                        ->has(Branch::factory()->count(2)))
                    ->create();

        $this->actingAs($user);

        $company = $user->companies()->inRandomOrder()->first();

        for ($i = 0; $i < 3; $i++) {
            $employee = Employee::factory()
                            ->for($company)
                            ->for(
                                User::factory()
                                    ->has(Profile::factory())
                                    ->hasAttached(Role::where('name', '=', UserRoles::USER->value)->first())
                                    ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
                                    ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
                                    ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
                            );

            $branches = $company->branches();
            $branchCount = $branches->count();

            if ($branchCount > 0) {
                $accessCount = random_int(1, $branchCount);
                $employee_branchs = $branches->inRandomOrder()->take($accessCount)->get();

                for ($j = 0; $j < $accessCount; $j++) {
                    $employee = $employee->has(EmployeeAccess::factory()->for($company)->for($employee_branchs[$j]));
                }
            }

            $employee->create();
        }

        $ulid = $company->employees()->inRandomOrder()->first()->ulid;

        $api = $this->getJson(route('api.get.db.company.employee.read', $ulid));

        $api->assertSuccessful();
    }

    public function test_employee_api_call_read_without_ulid_expect_exception()
    {
        $this->expectException(Exception::class);
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                        ->has(Branch::factory()->count(2)))
                    ->create();

        $this->actingAs($user);

        $this->getJson(route('api.get.db.company.employee.read', null));
    }

    public function test_employee_api_call_read_with_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                        ->has(Branch::factory()->count(2)))
                    ->create();

        $this->actingAs($user);

        $ulid = fake()->uuid();

        $api = $this->getJson(route('api.get.db.company.employee.read', $ulid));

        $api->assertStatus(404);
    }
}
