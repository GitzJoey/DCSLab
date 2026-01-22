<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\StockTransferProductUnit;
use Illuminate\Database\Seeder;

class StockTransferProductUnitSeeder extends Seeder
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
                $stockTransferProductUnitFactory = StockTransferProductUnit::factory()->for($company);
                $stockTransferProductUnitFactory->create();
            }
        }
    }
}
