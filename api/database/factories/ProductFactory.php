<?php

namespace Database\Factories;

use App\Enums\ProductType;
use App\Enums\RecordStatus;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    protected $productName = [
        'adjective' => ['Small', 'Ergonomic', 'Rustic', 'Intelligent', 'Gorgeous', 'Incredible', 'Fantastic', 'Practical', 'Sleek', 'Awesome', 'Enormous', 'Mediocre', 'Synergistic', 'Heavy Duty', 'Lightweight', 'Aerodynamic', 'Durable'],
        'material' => ['Steel', 'Wooden', 'Concrete', 'Plastic', 'Cotton', 'Granite', 'Rubber', 'Leather', 'Silk', 'Wool', 'Linen', 'Marble', 'Iron', 'Bronze', 'Copper', 'Aluminum', 'Paper'],
        'product' => ['Chair', 'Car', 'Computer', 'Gloves', 'Pants', 'Shirt', 'Table', 'Shoes', 'Hat', 'Plate', 'Knife', 'Bottle', 'Coat', 'Lamp', 'Keyboard', 'Bag', 'Bench', 'Clock', 'Watch', 'Wallet'],
    ];

    public function definition()
    {
        $product_type = fake()->randomElement(ProductType::toArrayValue());

        return [
            'code' => strtoupper(fake()->lexify()).fake()->numerify(),
            'name' => fake()->randomElement($this->productName['adjective']).' '.fake()->randomElement($this->productName['material']).' '.fake()->randomElement($this->productName['product']),
            'taxable_supply' => fake()->boolean(),
            'standard_rated_supply' => fake()->numberBetween(1, 10),
            'price_include_vat' => fake()->boolean(),
            'point' => fake()->numberBetween(0, 100),
            'product_type' => $product_type,
            'use_serial_number' => fake()->boolean(),
            'has_expiry_date' => fake()->boolean(),
            'status' => fake()->randomElement(RecordStatus::toArrayEnum()),
            'remarks' => fake()->word(),
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
        $text = fake()->randomElement($this->productName['adjective']).' '.fake()->randomElement($this->productName['material']).' '.fake()->randomElement($this->productName['product']);

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }

    public function setProductTypeAsProduct(ProductType $productType = null)
    {
        return $this->state(function (array $attributes) {
            return [
                'product_type' => fake()->randomElement([
                    ProductType::RAW_MATERIAL->value,
                    ProductType::WORK_IN_PROGRESS->value,
                    ProductType::FINISHED_GOODS->value,
                ]),
            ];
        });
    }

    public function setProductTypeAsService()
    {
        return $this->state(function (array $attributes) {
            return [
                'product_type' => ProductType::SERVICE->value,
            ];
        });
    }
}
