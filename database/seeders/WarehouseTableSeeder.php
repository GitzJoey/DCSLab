<?php

namespace Database\Seeders;

use App\Actions\RandomGenerator;
use App\Models\Warehouse;

use Illuminate\Database\Seeder;

class WarehouseTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i = 0; $i < 15; $i++)
        {
            $branch = Warehouse::factory()->make();

            $branch->save();
        }
    }
}
