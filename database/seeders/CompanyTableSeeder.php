<?php

namespace Database\Seeders;

use App\Actions\RandomGenerator;

use App\Models\User;
use App\Models\Company;

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CompanyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        $randomGenerator = new randomGenerator();

        foreach ($users as $user)
        {
            $rand = $randomGenerator->generateOne(5);

            $cIds = [];
            for($i = 0; $i < 2; $i++)
            {
                $comp = Company::factory()->make();

                if ($i == $rand) {
                    $comp->default = 1;
                }

                $comp->save();
                array_push($cIds, $comp->id);
            }

            $user->companies()->attach($cIds);
        }
    }
}
