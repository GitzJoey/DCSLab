<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Investor;
use Illuminate\Database\Seeder;

class InvestorSeeder extends Seeder
{
    public function run(?int $investorsPerCompany = null, ?int $companyId = null)
    {
        $investorsPerCompany = $investorsPerCompany ?? 5;

        $companies = $companyId ? Company::where('id', $companyId)->get() : Company::all();

        foreach ($companies as $company) {
            for ($i = 0; $i < $investorsPerCompany; $i++) {
                Investor::factory()
                    ->for($company)
                    ->create();
            }
        }
    }
}
