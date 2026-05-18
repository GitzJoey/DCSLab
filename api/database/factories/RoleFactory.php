<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $text = str_replace(' ', '-', fake()->jobTitle());

        return [
            'name' => str_replace(' ', '', strtolower($text)).fake()->numerify('#####'),
            'display_name' => $text,
            'description' => fake()->catchPhrase(),
        ];
    }
}
