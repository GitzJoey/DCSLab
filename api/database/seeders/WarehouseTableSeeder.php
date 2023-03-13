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

                Warehouse::factory()->for($company)->for($branch)->create();
            }
        }
    }
}
