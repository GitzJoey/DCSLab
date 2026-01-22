<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\PurchaseOrderDownPaymentApply;
use Illuminate\Database\Seeder;

class PurchaseOrderDownPaymentApplySeeder extends Seeder
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
                $purchaseOrderDownPaymentApplyFactory = PurchaseOrderDownPaymentApply::factory()->for($company);
                $purchaseOrderDownPaymentApplyFactory->create();
            }
        }
    }
}
