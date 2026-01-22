<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(?int $customersPerCompany = null, ?int $companyId = null)
    {
        $query = Company::query();
        if ($companyId) {
            $query->where('id', $companyId);
        }
        $companies = $query->get();

        $customersPerCompany = $customersPerCompany ?? 5;
        foreach ($companies as $company) {
            for ($i = 0; $i < $customersPerCompany; $i++) {
                Customer::factory()
                    ->for($company)
                    ->create();
            }
        }
    }
}
