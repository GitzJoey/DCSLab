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
            'tax_id' => $this->faker->randomDigit(),
            'ic_num' => $this->faker->randomDigit(),
            'img_path' => '',
            'status' => RecordStatus::INACTIVE,
            'remarks' => $this->faker->catchPhrase(),
        ];
    }

    public function setFirstName($first_name)
    {
        $country = ['Singapore', 'Indonesia'];
        shuffle($country);

        $profile = new Profile();

        $profile->first_name = $first_name;
        $profile->last_name = $this->faker->lastName();
        $profile->address = $this->faker->address();
        $profile->city = $this->faker->city();
        $profile->postal_code = $this->faker->postcode();
        $profile->country = $country[0];
        $profile->tax_id = $this->faker->randomDigit();
        $profile->ic_num = $this->faker->randomDigit();
        $profile->img_path = '';
        $profile->status = RecordStatus::INACTIVE;
        $profile->remarks = $this->faker->catchPhrase();

        return $profile;
    }

    public function setCreatedAt(Carbon $date = null)
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'created_at' => is_null($date) ? Carbon::now() : $date
            ];
        });
    }

    public function setUpdatedAt(Carbon $date = null)
    {
        return $this->state(function (array $attributes) use ($date) {
            return [
                'updated_at' => is_null($date) ? Carbon::now() : $date
            ];
        });
    }

    public function setStatusActive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => RecordStatus::ACTIVE
            ];
        });
    }

    public function setStatusInactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => RecordStatus::INACTIVE
            ];
        });
    }
}
