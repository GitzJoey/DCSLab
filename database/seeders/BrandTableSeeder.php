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
    public function run()
    {
        $faker = \Faker\Factory::create('id_ID');

        $brands = [
            ['name' => "Sampoerna"], 
            ['name' => "Bimoli"],
            ['name' => "Fortune"], 
            ['name' => "LA"],
            ['name' => "Tropical"], 
            ['name' => "Chitato"],
            ['name' => "Kriket"], 
            ['name' => "Tokai"],
            ['name' => "Aqua"],
            ['name' => "Indomie"],
            ['name' => "Cleo"],
            ['name' => "Peperro"],
            ['name' => "Segitiga Biru"],
            ['name' => "Asus"],
            ['name' => "Vivo"],
            ['name' => "Acer"],
            ['name' => "Dell"],
            ['name' => "HP"],
            ['name' => "Rucika"],
            ['name' => "Philips"],
            ['name' => "Miyako"],
            ['name' => "Cosmos"],
            ['name' => "Makita"],
            ['name' => "Rinnai"],
        ];

        $companies = Company::get()->pluck('id');

        foreach($companies as $company) {
            foreach ($brands as $brand) {
                $newBrand = new Brand();
                $newBrand->company_id = $company;
                $newBrand->code = $faker->unique()->numberBetween(001, 9999);
                $newBrand->name = $brand['name'];

                $newBrand->save();
            }
        }
    }
}
