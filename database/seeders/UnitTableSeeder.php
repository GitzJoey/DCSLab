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
        //ini unit2 ny di checkdi google
        //list of unit of measurement, check bahasa nya, tulisannya, etc

        $faker = \Faker\Factory::create('id_ID');

        $units = [
            ['code' => "", 'name' => "GR", 'category' => "1"],
            ['code' => "",'name' => "ONS", 'category' => "1"],
            ['code' => "", 'name' => "KG", 'category' => "1"],
            ['code' => "", 'name' => "MTR", 'category' => "1"],
            ['code' => "", 'name' => "PCS", 'category' => "1"],
            ['code' => "", 'name' => "PACK", 'category' => "1"],
            ['code' => "", 'name' => "SAK", 'category' => "1"],
            ['code' => "", 'name' => "KRT", 'category' => "1"],
            ['code' => "", 'name' => "BKS", 'category' => "1"],
            ['code' => "", 'name' => "SLOP", 'category' => "1"],
            ['code' => "", 'name' => "DUS", 'category' => "1"],
            ['code' => "", 'name' => "ROLL", 'category' => "1"],
            ['code' => "", 'name' => "BTG", 'category' => "1"],
            ['code' => "", 'name' => "m1", 'category' => "2"], // meter lari
            ['code' => "", 'name' => "m2", 'category' => "2"], // meter persegi
            ['code' => "", 'name' => "m3", 'category' => "3"], // meter kubik : bata ringan, pembangunan per volume
            ['code' => "", 'name' => "UNIT", 'category' => "3"],
            ['code' => "", 'name' => "TITIK", 'category' => "2"],
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
