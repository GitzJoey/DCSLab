<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\PurchaseReturnProductUnitSerial;
use Illuminate\Database\Seeder;

class PurchaseReturnProductUnitSerialSeeder extends Seeder
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
                $purchaseReturnProductUnitSerialFactory = PurchaseReturnProductUnitSerial::factory()->for($company);
                $purchaseReturnProductUnitSerialFactory->create();
            }
        }
    }
}
