<?php

namespace Tests\Feature\API\EmployeeAPI;

use App\Enums\UserRoles;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeAccess;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\APITestCase;
use Vinkla\Hashids\Facades\Hashids;

class EmployeeAPIEditTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_employee_api_call_update_without_authorization_expect_unauthorized_message()
    {
        $branchCount = 3;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $company = $user->companies->first();

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

        $employee = $company->employees()->inRandomOrder()->first();

        $accessCount = random_int(1, $company->branches()->count());
        $branches = $company->branches()->inRandomOrder()->take($accessCount)->get();
        $access_branch_hIds = [];
        for ($i = 0; $i < $accessCount; $i++) {
            array_push($access_branch_hIds, Hashids::encode($branches[$i]->id));
        }

        $userArr = User::factory()->make()->toArray();

        $profileArr = Profile::factory()->make()->toArray();

        $employeeArr = array_merge(
            [
                'company_id' => Hashids::encode($company->id),
                'arr_access_branch_id' => $access_branch_hIds,
            ],
            Employee::factory()->setStatusActive()->make()->toArray(),
            $userArr,
            $profileArr
        );

        $api = $this->json('POST', route('api.post.db.company.employee.edit', $employee->ulid), $employeeArr);

        $api->assertStatus(401);
    }

    public function test_employee_api_call_update_without_access_right_expect_unauthorized_message()
    {
        $branchCount = 3;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();

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

        $employee = $company->employees()->inRandomOrder()->first();

        $accessCount = random_int(1, $company->branches()->count());
        $branches = $company->branches()->inRandomOrder()->take($accessCount)->get();
        $access_branch_hIds = [];
        for ($i = 0; $i < $accessCount; $i++) {
            array_push($access_branch_hIds, Hashids::encode($branches[$i]->id));
        }

        $userArr = User::factory()->make()->toArray();

        $profileArr = Profile::factory()->make()->toArray();

        $employeeArr = array_merge(
            [
                'company_id' => Hashids::encode($company->id),
                'arr_access_branch_id' => $access_branch_hIds,
            ],
            Employee::factory()->setStatusActive()->make()->toArray(),
            $userArr,
            $profileArr
        );

        $api = $this->json('POST', route('api.post.db.company.employee.edit', $employee->ulid), $employeeArr);

        $api->assertStatus(403);
    }

    public function test_employee_api_call_update_with_script_tags_in_payload_expect_stripped()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_employee_api_call_update_with_script_tags_in_payload_expect_encoded()
    {
        $this->markTestSkipped('Test under construction');
    }

    public function test_employee_api_call_update_expect_successful()
    {
        $branchCount = 3;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();

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

        $employee = $company->employees()->inRandomOrder()->first();

        $accessCount = random_int(1, $company->branches()->count());
        $branches = $company->branches()->inRandomOrder()->take($accessCount)->get();
        $access_branch_hIds = [];
        for ($i = 0; $i < $accessCount; $i++) {
            array_push($access_branch_hIds, Hashids::encode($branches[$i]->id));
        }

        $userArr = User::factory()->make()->toArray();

        $profileArr = Profile::factory()->make()->toArray();

        $employeeArr = array_merge(
            [
                'company_id' => Hashids::encode($company->id),
                'arr_access_branch_id' => $access_branch_hIds,
            ],
            Employee::factory()->setStatusActive()->make()->toArray(),
            $userArr,
            $profileArr
        );

        $api = $this->json('POST', route('api.post.db.company.employee.edit', $employee->ulid), $employeeArr);

        $api->assertSuccessful();

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'company_id' => $company->id,
            'code' => $employeeArr['code'],
        ]);

        $this->assertDatabaseHas('users', [
            'name' => $employeeArr['name'],
            'email' => $employee->user()->first()->email,
        ]);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $employee->user()->first()->id,
            'address' => $employeeArr['address'],
            'city' => $employeeArr['city'],
            'postal_code' => $employeeArr['postal_code'],
            'country' => $employeeArr['country'],
            'tax_id' => $employeeArr['tax_id'],
            'ic_num' => $employeeArr['ic_num'],
            'status' => $employee->user()->first()->profile()->first()->status->value,
            'remarks' => $employeeArr['remarks'],
        ]);

        for ($i = 0; $i < $accessCount; $i++) {
            $this->assertDatabaseHas('employee_accesses', [
                'employee_id' => $employee->id,
                'branch_id' => Hashids::decode($access_branch_hIds[$i])[0],
            ]);
        }
    }

    public function test_employee_api_call_update_with_nonexistance_branch_id_expect_failed()
    {
        $branchCount = 3;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();

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

        $employee = $company->employees()->inRandomOrder()->first();

        $accessCount = random_int(1, $company->branches()->count());
        $branches = $company->branches()->inRandomOrder()->take($accessCount)->get();
        $access_branch_hIds = [];
        for ($i = 0; $i < $accessCount; $i++) {
            $branch_hId = Hashids::encode($branches[$i]->id);

            if ($i == 0) {
                $branch_hId = Hashids::encode(Branch::max('id') + 1);
            }

            array_push($access_branch_hIds, $branch_hId);
        }

        $userArr = User::factory()->make()->toArray();

        $profileArr = Profile::factory()->make()->toArray();

        $employeeArr = array_merge(
            [
                'company_id' => Hashids::encode($company->id),
                'arr_access_branch_id' => $access_branch_hIds,
            ],
            Employee::factory()->setStatusActive()->make()->toArray(),
            $userArr,
            $profileArr
        );

        $api = $this->json('POST', route('api.post.db.company.employee.edit', $employee->ulid), $employeeArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_employee_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        $branchCount = 3;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $this->actingAs($user);

        $company = $user->companies->first();

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

        $employees = $company->employees()->inRandomOrder()->take(2)->get();
        $employee_1 = $employees[0];
        $employee_2 = $employees[1];

        $accessCount = random_int(1, $company->branches()->count());
        $branches = $company->branches()->inRandomOrder()->take($accessCount)->get();
        $access_branch_hIds = [];
        for ($i = 0; $i < $accessCount; $i++) {
            array_push($access_branch_hIds, Hashids::encode($branches[$i]->id));
        }

        $userArr = User::factory()->make()->toArray();

        $profileArr = Profile::factory()->make()->toArray();

        $employeeArr = array_merge(
            [
                'company_id' => Hashids::encode($company->id),
                'arr_access_branch_id' => $access_branch_hIds,
            ],
            Employee::factory()->setStatusActive()->make([
                'code' => $employee_1->code,
            ])->toArray(),
            $userArr,
            $profileArr
        );

        $api = $this->json('POST', route('api.post.db.company.employee.edit', $employee_2->ulid), $employeeArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_employee_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        $branchCount = 3;
        $idxMainBranchCompany1 = random_int(0, $branchCount - 1);
        $idxMainBranchCompany2 = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranchCompany1 ? true : false,
                        ]
                    ))
                ))
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranchCompany2 ? true : false,
                        ]
                    ))
                ))
            ->create();

        $this->actingAs($user);

        $companies = $user->companies()->take(2)->get();

        $company_1 = $companies[0];
        for ($i = 0; $i < 3; $i++) {
            $employee = Employee::factory()
                ->for($company_1)
                ->for(
                    User::factory()
                        ->has(Profile::factory())
                        ->hasAttached(Role::where('name', '=', UserRoles::USER->value)->first())
                        ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
                        ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
                        ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
                );

            $branches = $company_1->branches();
            $branchCount = $branches->count();

            if ($branchCount > 0) {
                $accessCount = random_int(1, $branchCount);
                $employee_branchs = $branches->inRandomOrder()->take($accessCount)->get();

                for ($j = 0; $j < $accessCount; $j++) {
                    $employee = $employee->has(EmployeeAccess::factory()->for($company_1)->for($employee_branchs[$j]));
                }
            }

            $employee->create();
        }

        $company_2 = $companies[1];
        for ($i = 0; $i < 3; $i++) {
            $employee = Employee::factory()
                ->for($company_2)
                ->for(
                    User::factory()
                        ->has(Profile::factory())
                        ->hasAttached(Role::where('name', '=', UserRoles::USER->value)->first())
                        ->has(Setting::factory()->createDefaultSetting_PREF_THEME())
                        ->has(Setting::factory()->createDefaultSetting_PREF_DATE_FORMAT())
                        ->has(Setting::factory()->createDefaultSetting_PREF_TIME_FORMAT())
                );

            $branches = $company_2->branches();
            $branchCount = $branches->count();

            if ($branchCount > 0) {
                $accessCount = random_int(1, $branchCount);
                $employee_branchs = $branches->inRandomOrder()->take($accessCount)->get();

                for ($j = 0; $j < $accessCount; $j++) {
                    $employee = $employee->has(EmployeeAccess::factory()->for($company_2)->for($employee_branchs[$j]));
                }
            }

            $employee->create();
        }

        $employee = $company_1->employees()->inRandomOrder()->first();

        $accessCount = random_int(1, $company_2->branches()->count());
        $branches = $company_2->branches()->inRandomOrder()->take($accessCount)->get();
        $access_branch_hIds = [];
        for ($i = 0; $i < $accessCount; $i++) {
            array_push($access_branch_hIds, Hashids::encode($branches[$i]->id));
        }

        $userArr = User::factory()->make()->toArray();

        $profileArr = Profile::factory()->make()->toArray();

        $employeeArr = array_merge(
            [
                'company_id' => Hashids::encode($company_2->id),
                'arr_access_branch_id' => $access_branch_hIds,
            ],
            Employee::factory()->setStatusActive()->make([
                'code' => $employee->code,
            ])->toArray(),
            $userArr,
            $profileArr
        );

        $api = $this->json('POST', route('api.post.db.company.employee.edit', $employee->ulid), $employeeArr);

        $api->assertSuccessful();
    }
}
