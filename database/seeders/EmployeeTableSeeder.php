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
            $c = Branch::find($onlyThisBranchId);

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

                Employee::factory()->create([
                    'company_id' => $c,
                    'user_id' => $user->id
                ]);
            }
        }
    }
}
