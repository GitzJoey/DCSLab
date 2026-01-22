<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\PurchaseReturnAdditionalCost;
use Illuminate\Database\Seeder;

class PurchaseReturnAdditionalCostSeeder extends Seeder
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
                $purchaseReturnAdditionalCostFactory = PurchaseReturnAdditionalCost::factory()->for($company);
                $purchaseReturnAdditionalCostFactory->create();
            }
        }
    }
}
