<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class NonCapitalAdditionCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'name' => fake()->text(),
        ];
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
        $text = fake()->name();

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
