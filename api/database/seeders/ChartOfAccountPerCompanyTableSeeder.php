<?php

namespace Database\Seeders;

use App\Actions\ChartOfAccount\ChartOfAccountActions;
use App\Models\Company;
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
            $companies = Company::where('id', '=', $onlyThisCompanyId)->get();
        } else {
            $companies = Company::get();
        }

        $chartOfAccountActions = new ChartOfAccountActions();

        foreach ($companies as $company) {
            $chartOfAccountActions->createDefaultAccountPerCompany($company);
        }
    }
}
