<?php

namespace Database\Factories;

use App\Models\Warehouse;
use App\Models\Company;

use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Warehouse::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = \Faker\Factory::create('id_ID');
        $warehouse_name = $faker->city();

        return [
            'code' => $faker->numberBetween(01, 10),
            'name' => 'Gudang '.$warehouse_name,
            'address' => $faker->address(),
            'city' => $warehouse_name,
            'contact' => $faker->e164PhoneNumber(),
            'remarks' => '',
            'status' => 1
        ];
    }
}
