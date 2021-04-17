<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $text = str_replace(' ', '_', $this->faker->jobTitle());
        return [
            'name' => strtolower($text),
            'display_name' => $text,
            'description' => $this->faker->catchPhrase()
        ];
    }
}
