<?php

namespace Database\Seeders;

use App\Models\CapitalWithdrawal;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CapitalWithdrawalSeeder extends Seeder
{
    public function run(?int $companyId, ?int $qtyPerCompany)
    {
        $query = Company::query();
        if ($companyId) {
            $query->where('id', '=', $companyId);
        }
        $companies = $query->get();

        if (! $qtyPerCompany) {
            $qtyPerCompany = 5;
        }
        foreach ($companies as $company) {
            for ($i = 0; $i < $qtyPerCompany; $i++) {
                $capitalWithdrawalFactory = CapitalWithdrawal::factory()->for($company);
                $capitalWithdrawalFactory->create();
            }
        }
    }
}
