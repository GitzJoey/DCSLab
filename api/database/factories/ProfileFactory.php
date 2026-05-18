<?php

namespace Database\Factories;

use App\Enums\RecordStatus;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'     => Profile::factory()->belongsTo(User::class),
            'first_name'  => fake()->firstName(),
            'last_name'   => fake()->lastName(),
            'address'     => fake()->address(),
            'city'        => fake()->city(),
            'postal_code' => fake()->postcode(),
            'country'     => fake()->randomElement(['Singapore', 'Indonesia']),
            'tax_id'      => (string) fake()->numericToken(12),
            'ic_num'      => (string) fake()->numericToken(12),
            'img_path'    => null,
            'status'      => RecordStatus::INACTIVE,
            'remarks'     => fake()->catchPhrase(),
        ];
    }

    public function withFirstName(string $name): static
    {
        return $this->state(fn () => [
            'first_name' => $name,
        ]);
    }

    public function withTimestamps(?Carbon $createdAt = null, ?Carbon $updatedAt = null): static
    {
        return $this->state(fn () => [
            'created_at' => $createdAt ?? now(),
            'updated_at' => $updatedAt ?? now(),
        ]);
    }

    public function setCreatedAt(?Carbon $date = null)
    {
        return $this->state(function () use ($date) {
            return [
                'created_at' => is_null($date) ? Carbon::now() : $date,
            ];
        });
    }

    public function setUpdatedAt(?Carbon $date = null)
    {
        return $this->state(function () use ($date) {
            return [
                'updated_at' => is_null($date) ? Carbon::now() : $date,
            ];
        });
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'status' => RecordStatus::ACTIVE,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'status' => RecordStatus::INACTIVE,
        ]);
    }
}
