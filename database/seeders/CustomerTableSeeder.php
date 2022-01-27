<?php

namespace Database\Seeders;

use App\Actions\RandomGenerator;
use App\Models\Customer;
use App\Models\ProductUnit;
use Illuminate\Database\Seeder;

class CustomerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i = 0; $i < 10; $i++)
        {
            $faker = \Faker\Factory::create('id_ID');

            $customer = Customer::factory()->make();

            $customer->save();

            $customer_addresses = [];
            $nCount = (new RandomGenerator())->generateNumber(1,5);
            for ($j = 0; $j < $nCount; $j++) {
    
                array_push($customer_addresses, new ProductUnit(array(
                    'company_id' => $customer->company_id,
                    'customer_id' => $customer->id,
                    'address' => $faker->address(),
                    'city' => $faker->city(),
                    'contact' => $faker->phoneNumber(),
                    'remarks' => ''
                )));
            }
            $customer->customerAddress()->saveMany($customer_addresses);
        }
    }
}