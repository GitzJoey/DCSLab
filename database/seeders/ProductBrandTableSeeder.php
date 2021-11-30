<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ProductBrand;
use Illuminate\Database\Seeder;

class ProductBrandTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($brandPerCompany = 15)
    {
        $companies = Company::get()->pluck('id');

        foreach($companies as $c)
        {
            ProductBrand::factory()->count($brandPerCompany)->create([
                'company_id' => $c
            ]);
        }
    }
}
