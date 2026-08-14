<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CustomerGroup;
use Illuminate\Database\Seeder;

class CustomerGroupSeeder extends Seeder
{
    public function run(?int $customerGroupsPerCompany = null, ?int $companyId = null)
    {
        $customerGroupsPerCompany = $customerGroupsPerCompany ?? 5;

        $companies = $companyId ? Company::where('id', $companyId)->get() : Company::all();

        foreach ($companies as $company) {
            for ($i = 0; $i < $customerGroupsPerCompany; $i++) {
                CustomerGroup::factory()
                    ->for($company)
                    ->create();
            }
        }
    }
}
