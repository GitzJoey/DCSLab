<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Database\Seeder;

class ProductUnitTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $products = Product::all();

        foreach ($products as $product)
        {
            $productUnit = ProductUnit::factory()->make();
            $productUnit->product_id = $product->id;

            $productUnit->save();
        }
    }
}
