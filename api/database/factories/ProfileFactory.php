<?php

namespace Database\Factories;

use App\Enums\RecordStatus;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Profile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $country = ['Singapore', 'Indonesia'];
        shuffle($country);

        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country' => $country[0],
            'tax_id' => fake()->numberBetween(100000000000, 999999999999),
            'ic_num' => fake()->numberBetween(100000000000, 999999999999),
            'img_path' => '',
            'status' => RecordStatus::INACTIVE,
            'remarks' => fake()->catchPhrase(),
        ];
    }

    public function setFirstName(string $name)
    {
        return $this->state(function (array $attributes) use ($name) {
            return [
                'first_name' => $name,
            ];
        });
    }

    public function setCreatedAt(Carbon $date = null)
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'created_at' => is_null($date) ? Carbon::now() : $date,
            ];
        });
    }

    public function setUpdatedAt(Carbon $date = null)
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'updated_at' => is_null($date) ? Carbon::now() : $date,
            ];
        });
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
}
