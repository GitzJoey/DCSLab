<?php

namespace Database\Seeders;

use App\Models\Branch;

use Illuminate\Database\Seeder;

class BranchTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i = 0; $i < 5; $i++)
        {
            $branch = Branch::factory()->make();

            $branch->save();
        }
    }
}
