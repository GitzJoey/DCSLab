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
            // foreach ($productGroups as $productGroup) {
            //     $newProductGroup = new ProductGroup();
            //     $newProductGroup->company_id = $company;
            //     $newProductGroup->code = $faker->unique()->numberBetween(001, 9999);
            //     $newProductGroup->name = $productGroup['name'];
            //     $newProductGroup->category = $productGroup['category'];

            //     $newProductGroup->save();
            // }

            for($i = 0; $i < 10000; $i++)
            {
                $supplier = Supplier::factory()->make([
                    'company_id' => $company,
                ]);

                $supplier->save();
            }
        }

        // for($i = 0; $i < 100; $i++)
        // {
        //     $supplier = Supplier::factory()->make([
        //         'business_id' => $business->id,
        //     ]);

        //     $supplier->save();

        // }
    }
}
