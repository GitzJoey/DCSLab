<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ProductGroup;
use Illuminate\Database\Seeder;

class ProductGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($productGroupPerCompany = 5)
    {
        $companies = Company::get()->pluck('id');

        foreach($companies as $c)
        {
            ProductGroup::factory()->count($productGroupPerCompany)->create([
                'company_id' => $c
            ]);
        }
    }
}
