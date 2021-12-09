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
    public function run()
    {
        // ProductGroup::factory()->count(10)->create();

        $faker = \Faker\Factory::create('id_ID');

        $productGroups = [
            ['name' => "Rokok", 'category' => "1"],
            ['name' => "Minyak", 'category' => "1"],
            ['name' => "Beras", 'category' => "1"],
            ['name' => "Air Mineral", 'category' => "1"],
            ['name' => "Korek", 'category' => "1"],
            ['name' => "Mie", 'category' => "1"],
            ['name' => "Tepung Terigu", 'category' => "1"],
            ['name' => "Soft Drink", 'category' => "1"],
            ['name' => "Kopi", 'category' => "1"],
            ['name' => "Jasa Service Ringan", 'category' => "2"],
            ['name' => "Jasa Service Berat", 'category' => "2"],
            ['name' => "Jasa Pembangunan", 'category' => "2"],
            ['name' => "Jasa Renovasi", 'category' => "2"],
            ['name' => "Jasa Keuangan", 'category' => "2"],
            ['name' => "Jasa Audit", 'category' => "2"],
            ['name' => "Jasa Pengiriman", 'category' => "2"],
        ];

        $companies = Company::get()->pluck('id');

        foreach($companies as $company) {
            foreach ($productGroups as $productGroup) {
                $newProductGroup = new ProductGroup();
                $newProductGroup->company_id = $company;
                $newProductGroup->code = $faker->unique()->numberBetween(001, 999);
                $newProductGroup->name = $productGroup['name'];
                $newProductGroup->category = $productGroup['category'];

                $newProductGroup->save();
            }
        }
    }
}
