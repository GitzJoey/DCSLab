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
        $this->truncateCustomerGroupsTables();

        $instances = Container::getInstance();

        for ($i = 0; $i < 19; $i++) {
            try {
                $CustomerGroup = CustomerGroup::factory()->make();

                $CustomerGroup->created_at = Carbon::now();
                $CustomerGroup->updated_at = Carbon::now();

                $CustomerGroup->save();

                $Cash = $instances->make(CustomerGroup::class)->createDefaultCash();

                $CustomerGroup->Cashes()->saveMany($Cash);
            } catch (Exception $e) {

            }
        }
    }

    public function truncateCustomerGroupsTables()
    {
        Schema::disableForeignKeyConstraints();

        DB::table('cashes')->truncate();

        Schema::enableForeignKeyConstraints();
    }
}
