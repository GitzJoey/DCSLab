<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\SaleOrderProductUnit;
use Illuminate\Database\Seeder;

class SaleOrderProductUnitSeeder extends Seeder
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
                $saleOrderProductUnitFactory = SaleOrderProductUnit::factory()->for($company);
                $saleOrderProductUnitFactory->create();
            }
        }
    }
}
