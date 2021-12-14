<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create('id_ID');

        $units = [
            ['name' => "GR", 'category' => "1"],
            ['name' => "ONS", 'category' => "1"],
            ['name' => "KG", 'category' => "1"],
            ['name' => "MTR", 'category' => "1"],
            ['name' => "PCS", 'category' => "1"],
            ['name' => "PACK", 'category' => "1"],
            ['name' => "SAK", 'category' => "1"],
            ['name' => "KRT", 'category' => "1"],
            ['name' => "BKS", 'category' => "1"],
            ['name' => "SLOP", 'category' => "1"],
            ['name' => "DUS", 'category' => "1"],
            ['name' => "ROLL", 'category' => "1"],
            ['name' => "BTG", 'category' => "1"],
            ['name' => "m1", 'category' => "2"],
            ['name' => "m2", 'category' => "2"],
            ['name' => "m3", 'category' => "3"],
            ['name' => "UNIT", 'category' => "3"],
            ['name' => "TITIK", 'category' => "2"],
        ];

        $companies = Company::get()->pluck('id');

        foreach($companies as $company) {
            foreach ($units as $unit) {
                $newUnit = new Unit();
                $newUnit->company_id = $company;
                $newUnit->code = $faker->unique()->numberBetween(001, 999);
                $newUnit->name = $unit['name'];
                $newUnit->category = $unit['category'];

                $newUnit->save();
            }
        }
    }
}
