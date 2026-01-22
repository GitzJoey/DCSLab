<?php

namespace Database\Factories;

use App\Enums\UnitTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $units = [
        'PCS', 'SET', 'BTL', 'KG', 'LITRE', 'BOX', 'PACK', 'DOZEN',
        'METER', 'ROLL', 'CAN', 'DRUM', 'BAG', 'TUBE', 'JAR', 'GRAM',
        'LSN', 'KRT', 'BKS', 'SLOP',
    ];

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'name' => fake()->randomElement($this->units),
            'description' => fake()->sentence(),
            'type' => fake()->randomElement(UnitTypeEnum::toArrayEnum()),
        ];
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
        $text = fake()->randomElement($this->units);

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
