<?php

namespace Database\Factories;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $userName = fake()->username().random_int(1000, 9999);

        return [
            'name' => $userName,
            'email' => $userName.'@'.fake()->domainName(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
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

    public function setNotRequiredResetPassword()
    {
        return $this->state(function (array $attributes) {
            return [
                'password_changed_at' => Carbon::now(),
            ];
        });
    }

    public function setName($name)
    {
        return $this->state(function (array $attributes) use ($name) {
            return [
                'name' => strtolower(str_replace(' ', '', $name)),
            ];
        });
    }
}
