<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Seeder;

class BranchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($branchPerCompanies = 3, $onlyThisCompanyId = 0)
    {
        if ($onlyThisCompanyId != 0) {
            $companies = Company::where('id', '=', $onlyThisCompanyId)->get();
        } else {
            $companies = Company::get();
        }

        foreach ($companies as $company) {
            $rand = random_int(0, $branchPerCompanies - 1);

            for ($i = 0; $i < $branchPerCompanies; $i++) {
                $makeItActiveStatus = boolval(random_int(0, 1));

                $branchFactory = Branch::factory()->for($company);

                if ($i == $rand) {
                    $branchFactory->setIsMainBranch()->setStatusActive()->create();
                } else {
                    if ($makeItActiveStatus) {
                        $branchFactory->setStatusActive()->create();
                    } else {
                        $branchFactory->setStatusInactive()->create();
                    }
                }
            }
        }
    }
}
