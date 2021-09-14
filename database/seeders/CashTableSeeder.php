<?php

namespace Database\Seeders;

use App\Models\Cash;
use App\Models\CustomerGroup;
use App\Services\CustomerGroupService;
use Illuminate\Container\Container;
use Exception;
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
        $cash = Cash::factory()->count(5)->create();
    }
}
