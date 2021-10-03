<?php

namespace Database\Factories;

use App\Models\Profile;
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
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'address' => $this->faker->address(),
            'city' => $this->faker->city(),
            'postal_code' => $this->faker->postcode(),
            'country' => $this->faker->country(),
            'tax_id' => $this->faker->randomDigit(),
            'ic_num' => $this->faker->randomDigit(),
            'img_path' => '',
            'status' => 0,
            'remarks' => $this->faker->catchPhrase(),
        ];
    }

    public function setFirstName($first_name)
    {
        $profile = new Profile();

        $profile->first_name = $first_name;
        $profile->last_name = $this->faker->lastName();
        $profile->address = $this->faker->address();
        $profile->city = $this->faker->city();
        $profile->postal_code = $this->faker->postcode();
        $profile->country = $this->faker->country();
        $profile->tax_id = $this->faker->randomDigit();
        $profile->ic_num = $this->faker->randomDigit();
        $profile->img_path = '';
        $profile->status = 0;
        $profile->remarks = $this->faker->catchPhrase();

        return $profile;
    }
}
