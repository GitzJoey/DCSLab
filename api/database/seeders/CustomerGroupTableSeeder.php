<?php

namespace Database\Seeders;

use App\Actions\CustomerGroup\CustomerGroupActions;
use App\Models\Company;
use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class CustomerGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($countPerCompany = 3, $onlyThisCompanyId = 0)
    {
        if ($onlyThisCompanyId != 0) {
            $companies = Company::where('id', '=', $onlyThisCompanyId)->get();
        } else {
            $companies = Company::get();
        }

        foreach ($companies as $company) {
            $countPerCompany = $countPerCompany < 1 ? 1 : $countPerCompany;
            CustomerGroup::factory()->for($company)->count($countPerCompany)->create();
        }
    }
}
