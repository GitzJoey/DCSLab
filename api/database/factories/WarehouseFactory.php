<?php

namespace Database\Factories;

use App\Enums\RecordStatus;
use App\Models\Warehouse;
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
            'code' => strtoupper($this->faker->lexify()).$this->faker->numerify(),
            'name' => 'Gudang '.$warehouse_name,
            'address' => $faker->address(),
            'city' => $warehouse_name,
            'contact' => $faker->e164PhoneNumber(),
            'remarks' => $faker->word(),
            'status' => RecordStatus::ACTIVE,
        ];
    }

    public function setStatusActive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => RecordStatus::ACTIVE,
            ];
        });
    }

    public function setStatusInactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => RecordStatus::INACTIVE,
            ];
        });
    }

    public function insertStringInName(string $str)
    {
        return $this->state(function (array $attributes) use ($str) {
            return [
                'name' => $this->craftName($str),
            ];
        });
    }

    private function craftName(string $str)
    {
        $faker = \Faker\Factory::create('id_ID');
        $text = 'Gudang '.$faker->city();

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
