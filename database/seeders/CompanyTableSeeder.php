<?php

namespace Database\Seeders;

use App\Actions\RandomGenerator;

use App\Models\User;
use App\Models\Company;

use Illuminate\Database\Seeder;

class CompanyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companiesPerUsers = 3)
    {
        $users = User::all();

        $randomGenerator = new randomGenerator();

        foreach ($users as $user)
        {
            $rand = $randomGenerator->generateOne($companiesPerUsers);

            $cIds = [];
            for($i = 0; $i < $companiesPerUsers; $i++)
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
