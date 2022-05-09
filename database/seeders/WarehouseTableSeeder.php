<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use App\Models\Company;
use App\Enums\UserRoles;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use App\Actions\RandomGenerator;
use Illuminate\Database\Eloquent\Collection;

class WarehouseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($warehousePerBranches = 3, $onlyThisCompanyId = 0, $onlyThisBranchId = 0)
    {
        $userCount = User::count();
        if ($userCount == 0) {
            $userSeeder = new UserTableSeeder();
            $userSeeder->callWith(UserTableSeeder::class, [false, 1, UserRoles::USER]);
            $user = User::inRandomOrder()->first();
        } else {
            $user = User::inRandomOrder()->first();
        }

        $companyCount = Company::count();
        if ($companyCount == 0) {
            $companySeeder = new CompanyTableSeeder();
            $companySeeder->callWith(CompanyTableSeeder::class, [1, 0]);
            $companyId = $user->companies->random(1)->first()->id;
        } else {
            $companyId = $user->companies->random(1)->first()->id;
        }

        $branchSeeder = new BranchTableSeeder();
        $branchSeeder->callWith(BranchTableSeeder::class, [1, $companyId]);
        $branchId = $user->companies->random(1)->first()->branches->random(1)->first()->id;

        if ($onlyThisCompanyId != 0) {
            $c = Company::find($onlyThisCompanyId);

            if ($c) {
                $companies = (new Collection())->push($c->id);
            } else {
                $companies = Company::get()->pluck('id');
            }
        } else {
            $companies = Company::get()->pluck('id')->random(1);
        }

        $companyId = $companies->first();

        if ($onlyThisBranchId != 0) {
            $b = Branch::find($onlyThisBranchId);

            if ($b) {
                $branches = (new Collection())->push($b->id);
            } else {
                $branches = Branch::whereIn('company_id', $companyId)->get()->pluck('id');
            }
        } else {
            $branches = Branch::get()->pluck('id')->random(1);
        }

        foreach($companies as $c) {
            foreach($branches as $b) {
                for($i = 0; $i < $warehousePerBranches; $i++)
                {
                    $branch = Warehouse::factory()->create([
                        'company_id' => $c,
                        'branch_id' => $b
                    ]);    
                }
            }
        }
    }
}
