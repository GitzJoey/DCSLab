<?php

namespace Database\Factories;

use App\Enums\ProductGroupCategory;
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

    protected $productGroups = ['Books', 'Movies', 'Music', 'Games', 'Electronics', 'Computers', 'Home', 'Garden', 'Tools', 'Grocery', 'Health', 'Beauty', 'Toys', 'Kids', 'Baby', 'Clothing', 'Shoes', 'Jewelry', 'Sports', 'Outdoors', 'Automotive', 'Industrial'];

    public function definition()
    {
        $productGroupCategory = ProductGroupCategory::toArrayValue();

        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'name' => fake()->randomElement($this->productGroups),
            'category' => fake()->randomElement($productGroupCategory),
        ];
    }

    public function setCategoryToProduct()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => ProductGroupCategory::PRODUCTS,
            ];
        });
    }

    public function setCategoryToService()
    {
        return $this->state(function (array $attributes) {
            return [
                'category' => ProductGroupCategory::SERVICES,
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
        $text = fake()->randomElement($this->productGroups);

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
