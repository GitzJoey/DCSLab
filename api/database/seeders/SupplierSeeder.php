<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(?int $suppliersPerCompany = null, ?int $companyId = null)
    {
        $suppliersPerCompany = $suppliersPerCompany ?? 5;

        $companies = $companyId ? Company::where('id', $companyId)->get() : Company::all();

        foreach ($companies as $company) {
            for ($i = 0; $i < $suppliersPerCompany; $i++) {
                Supplier::factory()
                    ->for($company)
                    ->create();
            }
        }
    }
}
