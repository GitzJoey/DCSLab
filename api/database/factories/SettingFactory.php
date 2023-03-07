<?php

namespace Database\Factories;

use App\Models\Setting;
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
        $type = $this->faker->randomElement(['PREFS.THEME', 'PREFS.DATE_FORMAT', 'PREFS.TIME_FORMAT']);

        $val = match ($type) {
            'PREFS.THEME' => $this->faker->randomElement(['side-menu-light-full', 'side-menu-light-mini', 'side-menu-dark-full', 'side-menu-dark-mini']),
            'PREFS.DATE_FORMAT' => $this->faker->randomElement(['yyyy_MM_dd', 'dd_MMM_yyyy']),
            'PREFS.TIME_FORMAT' => $this->faker->randomElement(['hh_mm_ss', 'h_m_A']),
        };

        return [
            'type' => 'KEY_VALUE',
            'key' => $type,
            'value' => $val,
        ];
    }

    public function createDefaultSetting(): array
    {
        $list = [
            new Setting([
                'type' => 'KEY_VALUE',
                'key' => 'PREFS.THEME',
                'value' => 'side-menu-light-full',
            ]),
            new Setting([
                'type' => 'KEY_VALUE',
                'key' => 'PREFS.DATE_FORMAT',
                'value' => 'yyyy_MM_dd',
            ]),
            new Setting([
                'type' => 'KEY_VALUE',
                'key' => 'PREFS.TIME_FORMAT',
                'value' => 'hh_mm_ss',
            ]),
        ];

        return $list;
    }
}
