<?php

namespace Database\Factories;

use App\Actions\RandomGenerator;
use App\Enums\RecordStatus;
use App\Enums\ProductType;
use App\Models\Product;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = \Faker\Factory::create('id_ID');

        $product_type = $faker->randomElement(ProductType::toArrayValue());
        $status = $faker->randomElement(RecordStatus::toArrayValue());

        $productName = [
            'adjective' => ['Small', 'Ergonomic', 'Rustic', 'Intelligent', 'Gorgeous', 'Incredible', 'Fantastic', 'Practical', 'Sleek', 'Awesome', 'Enormous', 'Mediocre', 'Synergistic', 'Heavy Duty', 'Lightweight', 'Aerodynamic', 'Durable'],
            'material' => ['Steel', 'Wooden', 'Concrete', 'Plastic', 'Cotton', 'Granite', 'Rubber', 'Leather', 'Silk', 'Wool', 'Linen', 'Marble', 'Iron', 'Bronze', 'Copper', 'Aluminum', 'Paper'],
            'product' => ['Chair', 'Car', 'Computer', 'Gloves', 'Pants', 'Shirt', 'Table', 'Shoes', 'Hat', 'Plate', 'Knife', 'Bottle', 'Coat', 'Lamp', 'Keyboard', 'Bag', 'Bench', 'Clock', 'Watch', 'Wallet'],
        ];

        return [
            'code' => (new RandomGenerator())->generateFixedLengthNumber(5),
            'name' => $faker->randomElement($productName['adjective']).' '.$faker->randomElement($productName['material']).' '.$faker->randomElement($productName['product']),
            'taxable_supply' => $faker->boolean(),
            'standard_rated_supply' => $faker->numberBetween(1, 10),
            'price_include_vat' => $faker->boolean(),
            'remarks' => $faker->word(),
            'point' => $faker->numberBetween(1, 100),
            'product_type' => $product_type,
            'use_serial_number' => $faker->boolean(),
            'has_expiry_date' => $faker->boolean(),
            'status' => $this->faker->randomElement(RecordStatus::toArrayValue())
        ];
    }

    public function setStatusActive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => RecordStatus::ACTIVE
            ];
        });
    }

    public function setStatusInactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => RecordStatus::INACTIVE
            ];
        });
    }
}
