<?php

namespace Database\Seeders;

use App\Actions\Company\CompanyActions;
use App\DTOs\CompanyCreateDTO;
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
        $companyActions = app(CompanyActions::class);

        $users = $userId ? User::where('id', $userId)->get() : User::all();

        foreach ($users as $user) {
            $defaultCompanyData = Company::factory()
                ->setIsDefault()
                ->setStatusActive()
                ->make()
                ->toArray();

            $companyActions->resetDefault($user);
            $companyActions->create($user, new CompanyCreateDTO(
                code: $defaultCompanyData['code'],
                name: $defaultCompanyData['name'],
                address: $defaultCompanyData['address'],
                default: $defaultCompanyData['default'],
                status: $defaultCompanyData['status'],
            ));

            $remaining = max(0, $companiesPerUser - 1);

            for ($i = 0; $i < $remaining; $i++) {
                $company = Company::factory();

                random_int(0, 1) ? $company->setStatusActive() : $company->setStatusInactive();

                $companyActions->create($user, new CompanyCreateDTO(
                    code: $company->make()->toArray()['code'],
                    name: $company->make()->toArray()['name'],
                    address: $company->make()->toArray()['address'],
                    default: $company->make()->toArray()['default'],
                    status: $company->make()->toArray()['status'],
                ));
            }
        }
    }
}
