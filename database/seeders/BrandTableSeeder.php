<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandTableSeeder extends Seeder
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
            Brand::factory()->count($brandPerCompany)->create([
                'company_id' => $c
            ]);
        }
    }
}
