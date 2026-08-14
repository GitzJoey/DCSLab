<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(?int $branchesPerCompany = null, ?int $companyId = null)
    {
        $branchesPerCompany = $branchesPerCompany ?? 1;

        $companies = $companyId ? Company::where('id', $companyId)->get() : Company::all();

        foreach ($companies as $company) {
            Branch::factory()
                ->for($company)
                ->setIsMainBranch()
                ->setStatusActive()
                ->create();

            $remaining = max(0, $branchesPerCompany - 1);

            for ($i = 0; $i < $remaining; $i++) {
                $branch = Branch::factory()->for($company);

                random_int(0, 1) ? $branch->setStatusActive() : $branch->setStatusInactive();

                $branch->create();
            }
        }
    }
}
