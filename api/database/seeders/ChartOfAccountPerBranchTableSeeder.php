<?php

namespace Database\Seeders;

use App\Actions\ChartOfAccount\ChartOfAccountActions;
use App\Models\Company;
use Illuminate\Database\Seeder;

class ChartOfAccountPerBranchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($onlyThisCompanyId = 0)
    {
        if ($onlyThisCompanyId != 0) {
            $companies = Company::where('id', '=', $onlyThisCompanyId)->get();
        } else {
            $companies = Company::get();
        }

        $chartOfAccountActions = app(ChartOfAccountActions::class);

        foreach ($companies as $company) {
            $branches = $company->branches()->get();

            foreach ($branches as $branch) {
                $chartOfAccountActions->createDefaultAccountPerBranch($branch->company_id, $branch->id);
            }
        }
    }
}
