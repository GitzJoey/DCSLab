<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(?int $warehousesPerCompany = null, ?int $companyId = null)
    {
        $warehousesPerCompany = $warehousesPerCompany ?? 1;

        $companies = $companyId ? Company::where('id', $companyId)->get() : Company::all();

        foreach ($companies as $company) {
            $branch = Branch::where('company_id', $company->id)->inRandomOrder()->first();

            for ($i = 0; $i < $warehousesPerCompany; $i++) {
                $warehouse = Warehouse::factory()
                    ->for($company)
                    ->for($branch);

                random_int(0, 1) ? $warehouse->setStatusActive() : $warehouse->setStatusInactive();

                $warehouse->create();
            }
        }
    }
}
