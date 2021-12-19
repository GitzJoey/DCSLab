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
    public function run()
    {
        $companies = Company::get()->pluck('id');

        foreach($companies as $company) {
            for($i = 0; $i < 50; $i++)
            {
                $supplier = Supplier::factory()->make([
                    'company_id' => $company,
                ]);

                $supplier->save();
            }
        }
    }
}
