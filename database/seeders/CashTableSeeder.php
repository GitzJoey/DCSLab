<?php

namespace Database\Seeders;

use App\Models\Cash;
use Illuminate\Database\Seeder;

class CashTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Cash::factory()->count(5)->create();
    }
}
