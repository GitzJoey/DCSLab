<?php

namespace Database\Factories;

use App\Models\CustomerAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CustomerAddress>
 */
class CustomerAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @var string
     */
    protected $model = CustomerAddress::class;

    public function definition()
    {
        $locale = 'id_ID';

        return [
            'address' => fake($locale)->address(),
            'city' => fake($locale)->city(),
            'contact' => fake($locale)->e164PhoneNumber(),
            'is_main' => false,
            'remarks' => fake($locale)->sentence(),
        ];
    }

    public function setIsMain(bool $bool = true)
    {
        return $this->state(function (array $attributes) use ($bool) {
            return [
                'is_main' => $bool,
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
        $text = $this->faker->company();

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
