<?php

namespace Database\Seeders;

use App\Actions\RandomGenerator;
use App\Models\Company;
use App\Models\User;
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

        if ($companiesPerUsers <= 0) {
            $companiesPerUsers = 3;
        }

        foreach ($users as $user) {
            $rand = $randomGenerator->generateOne($companiesPerUsers - 1);

            $cIds = [];
            for ($i = 0; $i < $companiesPerUsers; $i++) {
                $makeItActiveStatus = (new RandomGenerator())->randomTrueOrFalse();
                if ($makeItActiveStatus) {
                    $comp = Company::factory()->setStatusActive()->make();
                } else {
                    $comp = Company::factory()->setStatusInactive()->make();
                }

                if ($i == $rand) {
                    $comp->default = 1;
                    $comp->status = 1;
                }

                $comp->save();
                array_push($cIds, $comp->id);
            }

            $user->companies()->attach($cIds);
        }
    }
}
