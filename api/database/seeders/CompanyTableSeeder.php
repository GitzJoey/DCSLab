<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
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
            $users = User::where('id', '=', $userId)->get();
        } else {
            $users = User::all();
        }

        if ($companiesPerUsers <= 0) {
            $companiesPerUsers = 3;
        }

        foreach ($users as $user) {
            $rand = random_int(0, $companiesPerUsers - 1);

            for ($i = 0; $i < $companiesPerUsers; $i++) {
                $makeItActiveStatus = boolval(random_int(0, 1));

                $companyFactory = Company::factory()->hasAttached($user);

                if ($i == $rand) {
                    $companyFactory->setIsDefault()->setStatusActive()->create();
                } else {
                    if ($makeItActiveStatus) {
                        $companyFactory->setStatusActive()->create();
                    } else {
                        $companyFactory->setStatusInactive()->create();
                    }
                }
            }
        }
    }
}
