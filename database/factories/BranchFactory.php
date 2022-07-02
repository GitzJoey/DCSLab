<?php

namespace Database\Factories;

use App\Actions\RandomGenerator;
use App\Enums\RecordStatus;
use App\Models\Branch;

use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Branch::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = \Faker\Factory::create('id_ID');
        $branch_name = $faker->city();

        return [
            'code' => (new RandomGenerator())->generateAlphaNumeric(5).(new RandomGenerator())->generateFixedLengthNumber(5),
            'name' => 'Kantor Cabang '.$faker->randomElement(['Utama', 'Pembantu', 'Daerah']).' '.$branch_name,
            'address' => $faker->address(),
            'city' => $branch_name,
            'contact' => $faker->e164PhoneNumber(),
            'is_main' => false,
            'remarks' => $faker->sentence(),
            'status' => $faker->randomElement(RecordStatus::toArrayEnum()),
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

    public function setIsMainBranch(bool $bool = true)
    {
        return $this->state(function (array $attributes) use ($bool) {
            return [
                'is_main' => $bool,
            ];
        });
    }
}
