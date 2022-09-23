<?php

namespace Database\Factories;

use App\Actions\RandomGenerator;
use App\Enums\ProductType;
use App\Enums\RecordStatus;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

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
        $faker = \Faker\Factory::create('id_ID');

        $product_type = $faker->randomElement(ProductType::toArrayValue());

        return [
            'code' => (new RandomGenerator())->generateAlphaNumeric(5).(new RandomGenerator())->generateFixedLengthNumber(5),
            'name' => $faker->randomElement($this->productName['adjective']).' '.$faker->randomElement($this->productName['material']).' '.$faker->randomElement($this->productName['product']),
            'taxable_supply' => $faker->boolean(),
            'standard_rated_supply' => $faker->numberBetween(1, 10),
            'price_include_vat' => $faker->boolean(),
            'remarks' => $faker->word(),
            'point' => $faker->numberBetween(0, 100),
            'product_type' => $product_type,
            'use_serial_number' => $faker->boolean(),
            'has_expiry_date' => $faker->boolean(),
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
        $faker = \Faker\Factory::create('id_ID');

        $text = $faker->randomElement($this->productName['adjective']).' '.$faker->randomElement($this->productName['material']).' '.$faker->randomElement($this->productName['product']);

        return substr_replace($text, $str, random_int(0, strlen($text) - 1), 0);
    }
}
