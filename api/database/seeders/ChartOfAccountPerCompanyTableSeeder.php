<?php

namespace Database\Seeders;

use App\Actions\ChartOfAccount\ChartOfAccountActions;
use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class ChartOfAccountPerCompanyTableSeeder extends Seeder
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
                $companies = (new Collection())->push($company->id);
            } else {
                $companies = Company::get()->pluck('id');
            }
        } else {
            $companies = Company::get()->pluck('id');
        }

        $chartOfAccountActions = app(ChartOfAccountActions::class);

        foreach ($companies as $company) {
            $chartOfAccountActions->createDefaultAccountPerCompany($company);
        }
    }
}
