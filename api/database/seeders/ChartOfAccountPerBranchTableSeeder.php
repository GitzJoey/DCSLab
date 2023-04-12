<?php

namespace Database\Seeders;

use App\Actions\ChartOfAccount\ChartOfAccountActions;
use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;
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
            $company = Company::find($onlyThisCompanyId);

            if ($company) {
                $companyIds = (new Collection())->push($company->id);
            } else {
                $companyIds = Company::get()->pluck('id');
            }
        } else {
            $companyIds = Company::get()->pluck('id');
        }

        $chartOfAccountActions = app(ChartOfAccountActions::class);

        foreach ($companyIds as $companyId) {
            $branches = Branch::where('company_id', '=', $companyId)->get();

            foreach ($branches as $branch) {
                $chartOfAccountActions->createDefaultAccountPerBranch($branch->company_id, $branch->id);
            }
        }
    }
}
