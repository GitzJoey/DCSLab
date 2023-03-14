<?php

namespace Database\Seeders;

use App\Enums\UserRoles;
use App\Models\Branch;
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

        if ($onlyThisBranchId != 0) {
            $branches = Branch::where('id', '=', $onlyThisBranchId)->get();
        } else {
            $branches = Branch::whereIn('company_id', $companies)->get();
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
                            )
                            ->create();

                $branchCount = $company->branches->count();

                if ($branchCount > 0) {
                    $arrEmployeeAccess = [];

                    $accessCount = random_int(1, $branchCount);
                    $branchIds = $company->branches()->inRandomOrder()->take($accessCount)->pluck('id');

                    for ($j = 0; $j < $accessCount; $j++) {
                        $employeeAccess = new EmployeeAccess;
                        $employeeAccess->employee_id = $employee->id;
                        $employeeAccess->branch_id = $branchIds[$j];
                        $employeeAccess->save();

                        array_push($arrEmployeeAccess, $employeeAccess);
                    }

                    $employee->employeeAccesses()->saveMany($arrEmployeeAccess);
                }
            }
        }
    }
}
