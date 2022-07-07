<?php

namespace Database\Factories;

use App\Actions\RandomGenerator;
use App\Enums\ProductCategory;
use App\Models\ProductGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProductGroup::class;

    protected $name = ['Books', 'Movies', 'Music', 'Games', 'Electronics', 'Computers', 'Home', 'Garden', 'Tools', 'Grocery', 'Health', 'Beauty', 'Toys', 'Kids', 'Baby', 'Clothing', 'Shoes', 'Jewelry', 'Sports', 'Outdoors', 'Automotive', 'Industrial',];

    public function definition()
    {
        return [
            'code' => (new RandomGenerator())->generateAlphaNumeric(5).(new RandomGenerator())->generateFixedLengthNumber(5),
            'name' => $this->faker->randomElement($this->name),
            'category' => $this->faker->randomElement(ProductCategory::toArrayValue()),
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
        $text = $this->faker->randomElement($this->name);

        return substr_replace($text, $str, strlen($text) - 1, 0);
    }
}
