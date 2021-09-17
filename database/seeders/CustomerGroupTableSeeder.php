<?php

namespace Database\Seeders;

use App\Models\Cash;
use App\Models\CustomerGroup;
use App\Services\CustomerGroupService;

use Exception;
use Carbon\Carbon;
use Illuminate\Container\Container;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CustomerGroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customergroup = CustomerGroup::factory()->make();
        $customergroup->created_at = Carbon::now();
        $customergroup->updated_at = Carbon::now();
        $customergroup = CustomerGroup::factory()->count(5)->create();
    }
}
