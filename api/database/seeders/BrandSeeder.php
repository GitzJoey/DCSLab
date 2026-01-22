<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Company;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(?int $brandsPerCompany = null, ?int $companyId = null)
    {
        $brandsPerCompany = $brandsPerCompany ?? 5;

        $companies = $companyId ? Company::where('id', $companyId)->get() : Company::all();

        foreach ($companies as $company) {
            for ($i = 0; $i < $brandsPerCompany; $i++) {
                Brand::factory()
                    ->for($company)
                    ->create();
            }
        }
    }
}
