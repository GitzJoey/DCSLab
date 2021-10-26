<?php

namespace Database\Seeders;

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
        for($i = 0; $i < 100; $i++)
        {
            $branch = Supplier::factory()->make();

            $branch->save();
        }
    }
}
