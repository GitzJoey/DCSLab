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
use Exception;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Str;
use Tests\APITestCase;

class EmployeeAPIDeleteTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_employee_api_call_delete_without_authorization_expect_unauthorized_message()
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

        $api = $this->json('POST', route('api.post.db.company.employee.delete', $employee->ulid));

        $api->assertStatus(401);
    }

    public function test_employee_api_call_delete_without_access_right_expect_unauthorized_message()
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

        $api = $this->json('POST', route('api.post.db.company.employee.delete', $employee->ulid));

        $api->assertStatus(403);
    }

    public function test_employee_api_call_delete_expect_successful()
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

        $api = $this->json('POST', route('api.post.db.company.employee.delete', $employee->ulid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('employees', [
            'id' => $employee->id,
        ]);
    }

    public function test_employee_api_call_delete_of_nonexistance_ulid_expect_not_found()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $ulid = Str::ulid()->generate();

        $api = $this->json('POST', route('api.post.db.company.employee.delete', $ulid));

        $api->assertStatus(404);
    }

    public function test_employee_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.db.company.employee.delete', null));
    }
}
