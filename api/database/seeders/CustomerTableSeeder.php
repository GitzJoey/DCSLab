<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Database\Seeder;

class CustomerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($countPerCompany = 3, $onlyThisCompanyId = 0)
    {
        if ($onlyThisCompanyId != 0) {
            $companies = Company::where('id', '=', $onlyThisCompanyId)->get();
        } else {
            $companies = Company::get();
        }

        foreach ($companies as $company) {
            $countPerCompany = $countPerCompany < 1 ? 1 : $countPerCompany;

            for ($i = 0; $i < $countPerCompany; $i++) {
                $customerGroup = $company->customerGroups()->inRandomOrder()->first();

                $customer = Customer::factory()
                            ->for($company)
                            ->for($customerGroup)
                            ->setIsMemberCustomer(boolval(random_int(0, 1)))
                            ->setTaxableEnterprise(boolval(random_int(0, 1)));

                $makeItActiveStatus = boolval(random_int(0, 1));
                if ($makeItActiveStatus) {
                    $customer = $customer->setStatusActive();
                } else {
                    $customer = $customer->setStatusInactive();
                }

                $addressCount = random_int(1, $countPerCompany);
                $isMainIdx = random_int(0, $addressCount - 1);

                for ($j = 0; $j < $addressCount; $j++) {
                    $customer = $customer->has(
                        CustomerAddress::factory()
                            ->for($company)
                            ->setIsMain($j == $isMainIdx ? true : false)
                    );
                }

                $customer->create();
            }
        }
    }
}
