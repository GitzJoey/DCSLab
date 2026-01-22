<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BrandFactory extends Factory
{
    protected $brands = [
        'Samsung', 'Huawei', 'LV', 'Apple', 'Xiaomi', 'Oppo',
        'Vivo', 'Google', 'OnePlus', 'Motorola', 'Nokia', 'Sony',
        'LG', 'TCL', 'Hisense', 'Sharp',
    ];

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'name' => fake()->randomElement($this->brands),
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
        $text = fake()->randomElement($this->brands);

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
