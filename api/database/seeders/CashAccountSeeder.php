<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\CashAccount;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CashAccountSeeder extends Seeder
{
    public function run(?int $cashAccountsPerCompany = null, ?int $companyId = null, ?int $branchId = null)
    {
        $cashAccountsPerCompany = $cashAccountsPerCompany ?? 5;

        $companies = $companyId ? Company::where('id', $companyId)->get() : Company::all();

        foreach ($companies as $company) {
            $branchQuery = Branch::where('company_id', $company->id);
            if ($branchId) $branchQuery->where('id', $branchId);
            $branch = $branchQuery->inRandomOrder()->first();
            if (! $branch) continue;

            for ($i = 0; $i < $cashAccountsPerCompany; $i++) {
                CashAccount::factory()
                    ->for($company)
                    ->for($branch)
                    ->create();
            }
        }
    }
}
