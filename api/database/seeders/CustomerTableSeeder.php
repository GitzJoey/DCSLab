<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class CustomerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($customerPerCompany = 5, $onlyThisCompanyId = 0)
    {
        if ($onlyThisCompanyId != 0) {
            $company = Company::find($onlyThisCompanyId);

            if ($company) {
                $companyIds = (new Collection())->push($company->id);
            } else {
                $companyIds = Company::get()->pluck('id');
            }
        } else {
            $companyIds = Company::get()->pluck('id');
        }

        $faker = \Faker\Factory::create();
        foreach ($companyIds as $companyId) {
            for ($i = 0; $i < $customerPerCompany; $i++) {
                $customerGroupId = CustomerGroup::whereCompanyId($companyId)->inRandomOrder()->first()->id;

                $customer = Customer::factory()->make([
                    'company_id' => $companyId,
                    'customer_group_id' => $customerGroupId,
                ]);

                $customer->save();

                $customerAddressCount = 5;
                for ($j = 0; $j < $customerAddressCount; $j++) {
                    $customerAddress = new CustomerAddress();

                    $customerAddress->company_id = $companyId;
                    $customerAddress->customer_id = $customer->id;
                    $customerAddress->address = $faker->address();
                    $customerAddress->city = $faker->city();
                    $customerAddress->contact = $faker->e164PhoneNumber();
                    $customerAddress->is_main = $j == 0 ? 1 : 0;
                    $customerAddress->remarks = $faker->sentence();

                    $customer->customerAddresses()->save($customerAddress);
                }
            }
        }
    }
}
