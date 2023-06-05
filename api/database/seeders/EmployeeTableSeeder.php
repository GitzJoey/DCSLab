<?php

namespace Database\Seeders;

use App\Enums\UserRoles;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeAccess;
use App\Models\Profile;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeTableSeeder extends Seeder
{
    public function run($employeePerPart = 3, $onlyThisCompanyId = 0, $onlyThisBranchId = 0)
    {
        if ($onlyThisCompanyId != 0) {
            $companies = Company::where('id', '=', $onlyThisCompanyId)->get();
        } else {
            $companies = Company::get();
        }

        foreach ($companies as $company) {
            for ($i = 0; $i < $employeePerPart; $i++) {
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

                if ($onlyThisBranchId != 0) {
                    $branches = $company->branches()->where('id', '=', $onlyThisBranchId)->get();
                } else {
                    $branches = $company->branches()->get();
                }

                $branchCount = $branches->count();

                if ($branchCount > 0) {
                    $accessCount = random_int(1, $branchCount);
                    $employee_branchs = $company->branches()->inRandomOrder()->take($accessCount)->get();

                    for ($j = 0; $j < $accessCount; $j++) {
                        $employee = $employee->has(EmployeeAccess::factory()->for($company)->for($employee_branchs[$j]));
                    }
                }

                $employee->create();
            }
        }
    }
}
