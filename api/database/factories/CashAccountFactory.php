<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CashAccountFactory extends Factory
{
    protected $cashAccounts = [
        'Cash', 'Bank', 'Giro',
    ];

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify()).' '.fake()->numerify(),
            'name' => fake()->randomElement($this->cashAccounts),
            'is_bank' => fake()->boolean(),
            'is_active' => fake()->boolean(),
            'remarks' => fake()->sentence(),
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
        $text = fake()->randomElement(['Cash', 'Bank', 'Giro']);

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
