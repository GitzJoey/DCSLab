<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(?int $companiesPerUser = null, ?int $userId = null)
    {
        $companiesPerUser = $companiesPerUser ?? 1;

        $users = $userId ? User::where('id', $userId)->get() : User::all();

        foreach ($users as $user) {
            Company::factory()
                ->hasAttached($user)
                ->setIsDefault()
                ->setStatusActive()
                ->create();

            $remaining = max(0, $companiesPerUser - 1);

            for ($i = 0; $i < $remaining; $i++) {
                $company = Company::factory()->hasAttached($user);

                random_int(0, 1) ? $company->setStatusActive() : $company->setStatusInactive();

                $company->create();
            }
        }
    }
}
