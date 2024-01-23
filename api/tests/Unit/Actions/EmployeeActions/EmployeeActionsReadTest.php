<?php

namespace Tests\Unit\Actions\EmployeeActions;

use App\Actions\Employee\EmployeeActions;
use App\Enums\UserRoles;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeAccess;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\ActionsTestCase;

class EmployeeActionsReadTest extends ActionsTestCase
{
    private EmployeeActions $employeeActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employeeActions = new EmployeeActions();
    }

    public function test_employee_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $branchCount = 5;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

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

        $result = $this->employeeActions->readAny(
            companyId: $company->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_employee_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $branchCount = 5;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

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

        $result = $this->employeeActions->readAny(
            companyId: $company->id,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_employee_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;
        $result = $this->employeeActions->readAny(
            companyId: $maxId,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_employee_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $branchCount = 5;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

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

        $result = $this->employeeActions->readAny(
            companyId: $company->id,
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 2);
    }

    public function test_employee_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $branchCount = 5;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        for ($i = 0; $i < 5; $i++) {
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

        $result = $this->employeeActions->readAny(
            companyId: $company->id,
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 5);
    }

    public function test_employee_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
            )->create();

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

        $result = $this->employeeActions->readAny(
            companyId: $company->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 3);
    }

    public function test_employee_actions_call_read_expect_object()
    {
        $branchCount = 5;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $employee = Employee::factory()->for($company)->for($user)
            ->has(User::factory()
                ->has(
                    Profile::factory()->for($user)->setStatusActive()
                ))->create();

        $branchCount = $company->branches()->count();
        $accessCount = random_int(1, $branchCount);
        $branches = $company->branches()->inRandomOrder()->take($accessCount)->get();
        for ($i = 0; $i < $accessCount; $i++) {
            EmployeeAccess::factory()
                ->for($company)->for($employee)->for($branches[$i])
                ->create();
        }

        $result = $this->employeeActions->read($employee);

        $this->assertInstanceOf(Employee::class, $result);
    }
}
