<?php

namespace Database\Seeders;

use App\Actions\RandomGenerator;
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
    public function run($warehousePerCompanies = 3)
    {
        $companies = Company::get()->pluck('id');

        foreach($companies as $c) {
            for($i = 0; $i < $warehousePerCompanies; $i++)
            {
                $branch = Warehouse::factory()->create([
                    'company_id' => $c
                ]);    
            }    
        }
    }
}
