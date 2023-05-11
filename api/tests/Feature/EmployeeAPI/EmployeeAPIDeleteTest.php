<?php

namespace Tests\Feature\API;

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
use Illuminate\Support\Str;
use App\Models\EmployeeAccess;

class EmployeeAPIDeleteTest extends APITestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_employee_api_call_delete_expect_successful()
    {
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                        ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                        ->has(Branch::factory()->count(2)))
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
