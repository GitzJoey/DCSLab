<?php

namespace Database\Factories;

use App\Actions\RandomGenerator;
use App\Enums\AccountType;
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
        $faker = \Faker\Factory::create('id_ID');

        return [
            'code' => (new RandomGenerator())->generateAlphaNumeric(5).(new RandomGenerator())->generateFixedLengthNumber(5),
            'name' => $this->faker->name(),
            'account_type' => $faker->randomElement(AccountType::toArrayEnum()),
            'remarks' => $faker->word(),
        ];
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
        $text = $this->faker->randomElement($this->chartOfAccounts);

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
