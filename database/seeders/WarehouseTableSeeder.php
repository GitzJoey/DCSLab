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
        $randomGenerator = new randomGenerator();
        $randomGenerator->generateOne(5);

        for($i = 0; $i < 5; $i++)
        {
            $branch = Warehouse::factory()->make();

            $branch->save();
        }
    }
}
