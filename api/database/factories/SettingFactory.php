<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $type = fake()->randomElement(['PREFS.THEME', 'PREFS.DATE_FORMAT', 'PREFS.TIME_FORMAT']);

        $val = match ($type) {
            'PREFS.THEME' => fake()->randomElement(['side-menu-light-full', 'side-menu-light-mini', 'side-menu-dark-full', 'side-menu-dark-mini']),
            'PREFS.DATE_FORMAT' => fake()->randomElement(['yyyy_MM_dd', 'dd_MMM_yyyy']),
            'PREFS.TIME_FORMAT' => fake()->randomElement(['hh_mm_ss', 'h_m_A']),
        };

        return [
            'type' => 'KEY_VALUE',
            'key' => $type,
            'value' => $val,
        ];
    }

    public function createDefaultSetting_PREF_THEME()
    {
        return $this->state(function (array $attributes) {
            return [
                'key' => 'PREFS.THEME',
                'value' => 'side-menu-light-full',
            ];
        });
    }

    public function createDefaultSetting_PREF_DATE_FORMAT()
    {
        return $this->state(function (array $attributes) {
            return [
                'key' => 'PREFS.DATE_FORMAT',
                'value' => 'yyyy_MM_dd',
            ];
        });
    }

    public function createDefaultSetting_PREF_TIME_FORMAT()
    {
        return $this->state(function (array $attributes) {
            return [
                'key' => 'PREFS.TIME_FORMAT',
                'value' => 'hh_mm_ss',
            ];
        });
    }
}
