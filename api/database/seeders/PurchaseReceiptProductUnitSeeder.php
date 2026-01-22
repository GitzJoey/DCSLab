<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\PurchaseReceiptProductUnit;
use Illuminate\Database\Seeder;

class PurchaseReceiptProductUnitSeeder extends Seeder
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
                $purchaseReceiptProductUnitFactory = PurchaseReceiptProductUnit::factory()->for($company);
                $purchaseReceiptProductUnitFactory->create();
            }
        }
    }
}
