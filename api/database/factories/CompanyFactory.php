<?php

namespace Database\Factories;

use App\Enums\RecordStatus;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $locale = 'id_ID';

        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'name' => fake($locale)->company(),
            'address' => fake($locale)->address(),
            'default' => false,
            'status' => RecordStatus::ACTIVE,
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
        $text = fake()->company();

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
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

    public function setIsDefault()
    {
        return $this->state(function (array $attributes) {
            return [
                'default' => true,
            ];
        });
    }
}
