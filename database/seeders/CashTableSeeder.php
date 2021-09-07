<?php

namespace Database\Seeders;

use App\Models\Cash;
use App\Models\CustomerGroup;
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
        Cash::factory()->count(15)->create();

        $Cash = Cash::factory()->create();
        $CustomerGroup = CustomerGroup::factory()->create();

        $Cash->customergroups()->saveMany($CustomerGroup);
    }
}
