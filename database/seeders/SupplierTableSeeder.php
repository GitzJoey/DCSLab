<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Supplier;

use Illuminate\Database\Seeder;

class SupplierTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($supplierPerCompany = 10)
    {
        $companies = Company::get()->pluck('id');

        foreach($companies as $c)
        {
            Supplier::factory()->count($supplierPerCompany)->create([
                'company_id' => $c
            ]);
        }
    }
}
