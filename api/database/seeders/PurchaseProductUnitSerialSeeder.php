<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\PurchaseProductUnitSerial;
use Illuminate\Database\Seeder;

class PurchaseProductUnitSerialSeeder extends Seeder
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
                $purchaseProductUnitSerialFactory = PurchaseProductUnitSerial::factory()->for($company);
                $purchaseProductUnitSerialFactory->create();
            }
        }
    }
}
