<?php

namespace Database\Seeders;

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
        $kg = new Unit();
        $kg->code = "001";
        $kg->name = "Kg";

        $kg->save();
    }
}
