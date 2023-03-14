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
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country' => $country[0],
            'tax_id' => $this->faker->numberBetween(100000000000, 999999999999),
            'ic_num' => $this->faker->numberBetween(100000000000, 999999999999),
            'img_path' => '',
            'status' => RecordStatus::INACTIVE,
            'remarks' => $this->faker->catchPhrase(),
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
