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
use Illuminate\Database\Seeder;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;

class EmployeeTableSeeder extends Seeder
{
    public function run($employeePerPart = 3, $onlyThisCompanyId = 0, $onlyThisBranchId = 0)
    {
        if ($onlyThisCompanyId != 0) {
            $c = Company::find($onlyThisCompanyId);

            if ($c) {
                $companies = (new Collection())->push($c->id);
            } else {
                $companies = Company::get()->pluck('id');
            }
        } else {
            $companies = Company::get()->pluck('id');
        }

        if ($onlyThisBranchId != 0) {
            $c = Branch::find($onlyThisCompanyId);

            if ($c) {
                $branches = (new Collection())->push($c->id);
            } else {
                $branches = Branch::get()->pluck('id');
            }
        } else {
            $branches = Branch::get()->pluck('id');
        }

        $container = Container::getInstance();
        $setting = $container->make(UserService::class)->createDefaultSetting();
        $roles = $container->make(RoleService::class)->readBy('NAME', UserRoles::USER->value);

        foreach($companies as $c) {
            for($i = 0; $i < $employeePerPart; $i++)
            {
                $employee = Employee::factory()->make();

                $user = User::factory()->make();
                $user->name = $employee->name;
                $user->created_at = Carbon::now();
                $user->updated_at = Carbon::now();
                $user->save();

                $name = $employee->name;
                $first_name = '';
                $last_name = '';
                if ($name == trim($name) && strpos($name, ' ') !== false) {
                    $pieces = explode(" ", $name);
                    $first_name = $pieces[0];
                    $last_name = $pieces[1];
                } else {
                    $first_name = $name;
                }
                $profile = Profile::factory()->make();
                $profile->first_name = $first_name;
                $profile->last_name = $last_name;
                $user->profile()->save($profile);
    
                $companyId = Company::inRandomOrder()->first()->id;
                $user->companies()->attach($companyId);
    
                $user->attachRoles([$roles->id]);
                $user->settings()->saveMany($setting);

                $employee->company_id = $companyId;
                $employee->user_id = $user->id;
                $employee->save;
            }
        }
    }
}
