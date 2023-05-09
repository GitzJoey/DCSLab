<?php

namespace Database\Factories;

use App\Actions\Randomizer\RandomizerActions;
use App\Enums\AccountType;
use App\Enums\RecordStatus;
use App\Models\ChartOfAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChartOfAccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ChartOfAccount::class;

    public function definition()
    {
        $locale = 'id_ID';

        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'name' => fake()->name(),
            'account_type' => fake($locale)->randomElement(AccountType::toArrayEnum()),
            'can_have_child' => true,
            'remarks' => fake($locale)->word(),
            'status' => fake($locale)->randomElement(RecordStatus::toArrayEnum()),
        ];
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

    public function insertStringInName(string $str)
    {
        return $this->state(function (array $attributes) use ($str) {
            return [
                'name' => $this->craftName($str),
            ];
        });
    }

    private function craftName(string $str)
    {
        $text = fake()->chartOfAccount();

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
