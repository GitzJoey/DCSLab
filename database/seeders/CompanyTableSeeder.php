<?php

namespace Database\Seeders;

use App\Actions\RandomGenerator;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class CompanyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($companiesPerUsers = 3, $userId = 0)
    {
        if ($userId != 0) {
            $usr = User::find($userId);

            if ($usr) {
                $users = (new Collection())->push($usr);
            } else {
                $users = User::all();
            }
        } else {
            $users = User::all();
        }
            
        $randomGenerator = new randomGenerator();

        if ($companiesPerUsers <= 0) $companiesPerUsers = 3;

        foreach ($users as $user)
        {
            $rand = $randomGenerator->generateOne($companiesPerUsers - 1);

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
