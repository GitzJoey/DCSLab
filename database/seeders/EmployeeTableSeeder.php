<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Profile;
use App\Enums\UserRoles;
use App\Models\Employee;
use App\Services\RoleService;
use App\Services\UserService;
use App\Models\EmployeeAccess;
use Illuminate\Database\Seeder;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;

class EmployeeTableSeeder extends Seeder
{
    use WithFaker;
 
    public function run($employeePerPart = 3, $onlyThisCompanyId = 0, $onlyThisBranchId = 0)
    {
        if ($onlyThisCompanyId != 0) {
            $company = Company::find($onlyThisCompanyId);

            if ($company) {
                $companyIds = $company->pluck('id');
            } else {
                $companyIds = Company::pluck('id');
            }
        } else {
            $companyIds = Company::pluck('id');
        }

        if ($onlyThisBranchId != 0) {
            $branch = Branch::find($onlyThisBranchId);
        
            if ($branch) {
                $branchIds = $branch->pluck('id');
            } else {
                $branchIds = Branch::whereIn('company_id', $companyIds)->pluck('id');
            }
        } else {
            $branchIds = Branch::whereIn('company_id', $companyIds)->pluck('id');
        }

        $container = Container::getInstance();
        $setting = $container->make(UserService::class)->createDefaultSetting();
        $roles = $container->make(RoleService::class)->readBy('NAME', UserRoles::USER->value);

        foreach($companyIds as $c) {
            for($i = 0; $i < $employeePerPart; $i++)
            {
                $user = User::factory()->make();
                $user->created_at = Carbon::now();
                $user->updated_at = Carbon::now();
                $user->save();

                $profile = Profile::factory()->setFirstName($user->name);
                $profile->created_at = Carbon::now();
                $profile->updated_at = Carbon::now();
                $user->profile()->save($profile);
                
                $user->companies()->attach($c);
                $user->attachRoles([$roles->id]);
                $user->settings()->saveMany($setting);

                $employee = Employee::factory()->create([
                    'company_id' => $c,
                    'user_id' => $user->id
                ]);
                
                $branchCount = Company::find($c)->branches->count();
                if ($branchCount > 0) {
                    $faker = \Faker\Factory::create('id_ID');
                    $accessCount = $faker->numberBetween(1, $branchCount);
                    $branchIds = Company::find($c)->branches()->inRandomOrder()->take($accessCount)->pluck('id');
                    for ($i = 0; $i < $accessCount ; $i++) {
                        $employeeAccess = new EmployeeAccess;
                        $employeeAccess->employee_id = $employee->id;
                        $employeeAccess->branch_id = $branchIds[$i];
                        $employeeAccess->save();
                    }
                }
               
            }
        }
    }
}
