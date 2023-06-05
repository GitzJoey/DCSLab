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
        $locale = 'id_ID';
        $warehouse_city = fake($locale)->city();

        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'name' => 'Gudang '.$warehouse_city,
            'address' => fake($locale)->address(),
            'city' => $warehouse_city,
            'contact' => fake($locale)->e164PhoneNumber(),
            'remarks' => fake($locale)->word(),
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

    public function setName($name)
    {
        return $this->state(function (array $attributes) use ($name) {
            return [
                'name' => $name,
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
        $text = 'Gudang '.fake('id_ID')->city();

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
