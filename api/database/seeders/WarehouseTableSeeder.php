<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($warehousePerCompanies = 3, $onlyThisCompanyId = 0)
    {
        if ($onlyThisCompanyId != 0) {
            $companies = Company::where('id', '=', $onlyThisCompanyId)->get();
        } else {
            $companies = Company::get();
        }

        foreach ($companies as $company) {
            for ($i = 0; $i < $warehousePerCompanies; $i++) {
                $branch = Branch::whereRelation('company', 'id', '=', $company)->inRandomOrder()->first();

                $makeItActiveStatus = boolval(random_int(0, 1));

                if ($makeItActiveStatus) {
                    Warehouse::factory()->for($company)->for($branch)->setStatusActive()->create();
                } else {
                    Warehouse::factory()->for($company)->for($branch)->setStatusInactive()->create();
                }

            }
        }
    }
}
