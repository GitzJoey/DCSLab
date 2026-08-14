<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\StockTransferItemSerial;
use Illuminate\Database\Seeder;

class StockTransferItemSerialSeeder extends Seeder
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
                $stockTransferItemSerialFactory = StockTransferItemSerial::factory()->for($company);
                $stockTransferItemSerialFactory->create();
            }
        }
    }
}
