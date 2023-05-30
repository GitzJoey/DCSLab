<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Company;
use Illuminate\Database\Seeder;

class BrandTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($countPerCompany = 15, $onlyThisCompanyId = 0)
    {
        if ($onlyThisCompanyId != 0) {
            $companies = Company::where('id', '=', $onlyThisCompanyId)->get();
        } else {
            $companies = Company::get();
        }

        foreach ($companies as $company) {
            Brand::factory()->for($company)->count($countPerCompany)->create();
        }
    }
}
