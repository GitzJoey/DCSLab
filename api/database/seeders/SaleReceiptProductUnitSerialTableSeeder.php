<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\SaleReceiptProductUnitSerial;
use Illuminate\Database\Seeder;

class SaleReceiptProductUnitSerialTableSeeder extends Seeder
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
                $saleReceiptProductUnitSerialFactory = SaleReceiptProductUnitSerial::factory()->for($company);
                $saleReceiptProductUnitSerialFactory->create();
            }
        }
    }
}
