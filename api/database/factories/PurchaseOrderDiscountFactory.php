<?php

namespace Database\Factories;

use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrderProductUnit>
 */
class PurchaseOrderDiscountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $locale = 'id_ID';

        $discountType = fake()->randomElement(DiscountType::toArrayValue());

        $amount = 0;
        if ($discountType == DiscountType::GLOBAL_PERCENT_DISCOUNT || $discountType == DiscountType::PER_UNIT_PERCENT_DISCOUNT || $discountType == DiscountType::PER_UNIT_SUBTOTAL_PERCENT_DISCOUNT) {
            $amount = random_int(1, 99) / 100;
        } elseif ($discountType == DiscountType::GLOBAL_NOMINAL_DISCOUNT || $discountType == DiscountType::PER_UNIT_NOMINAL_DISCOUNT || $discountType == DiscountType::PER_UNIT_SUBTOTAL_NOMINAL_DISCOUNT) {
            $amount = fake()->randomFloat(-4, 1000, 10000000);
        }

        return [
            'discount_type' => $discountType,
            'amount' => $amount,
        ];
    }

    public function setGlobalDiscountPercent()
    {
        return $this->state(function (array $attributes) {
            return [
                'discount_type' => DiscountType::GLOBAL_PERCENT_DISCOUNT,
                'amount' => random_int(1, 99) / 100,
            ];
        });
    }

    public function setGlobalDiscountNominal()
    {
        return $this->state(function (array $attributes) {
            return [
                'discount_type' => DiscountType::GLOBAL_NOMINAL_DISCOUNT,
                'amount' => fake()->randomFloat(-4, 1000, 10000000),
            ];
        });
    }

    public function setGlobalDiscountRandom()
    {
        $discountTypeArr = DiscountType::toArrayEnum();
        unset($discountTypeArr[0]);
        unset($discountTypeArr[1]);
        unset($discountTypeArr[2]);
        unset($discountTypeArr[3]);

        $discountType = fake()->randomElement($discountTypeArr);

        $amount = 0;
        if ($discountType == DiscountType::GLOBAL_PERCENT_DISCOUNT) {
            $amount = mt_rand(1, 99) / 100;
        } elseif ($discountType == DiscountType::GLOBAL_NOMINAL_DISCOUNT) {
            $amount = fake()->randomFloat(4, 0, 10000000);
        }

        return $this->state(function (array $attributes) use ($discountType, $amount) {
            return [
                'discount_type' => $discountType,
                'amount' => $amount,
            ];
        });
    }

    public function setPerUnitDiscountPercent()
    {
        return $this->state(function (array $attributes) {
            return [
                'discount_type' => DiscountType::PER_UNIT_PERCENT_DISCOUNT,
                'amount' => random_int(1, 99) / 100,
            ];
        });
    }

    public function setPerUnitDiscountNominal()
    {
        return $this->state(function (array $attributes) {
            return [
                'discount_type' => DiscountType::PER_UNIT_NOMINAL_DISCOUNT,
                'amount' => fake()->randomFloat(-4, 1000, 10000000),
            ];
        });
    }

    public function setPerUnitDiscountRandom()
    {
        $discountTypeArr = DiscountType::toArrayEnum();
        unset($discountTypeArr[2]);
        unset($discountTypeArr[3]);
        unset($discountTypeArr[4]);
        unset($discountTypeArr[5]);

        $discountType = fake()->randomElement($discountTypeArr);

        $amount = 0;
        if ($discountType == DiscountType::PER_UNIT_PERCENT_DISCOUNT) {
            $amount = mt_rand(1, 99) / 100;
        } elseif ($discountType == DiscountType::PER_UNIT_NOMINAL_DISCOUNT) {
            $amount = fake()->randomFloat(4, 0, 10000000);
        }

        return $this->state(function (array $attributes) use ($discountType, $amount) {
            return [
                'discount_type' => $discountType,
                'amount' => $amount,
            ];
        });
    }

    public function setPerUnitSubTotalDiscountPercent()
    {
        return $this->state(function (array $attributes) {
            return [
                'discount_type' => DiscountType::PER_UNIT_SUBTOTAL_PERCENT_DISCOUNT,
                'amount' => random_int(1, 99) / 100,
            ];
        });
    }

    public function setPerUnitSubTotalDiscountNominal()
    {
        return $this->state(function (array $attributes) {
            return [
                'discount_type' => DiscountType::PER_UNIT_SUBTOTAL_NOMINAL_DISCOUNT,
                'amount' => fake()->randomFloat(-4, 1000, 10000000),
            ];
        });
    }

    public function setPerUnitSubTotalDiscountRandom()
    {
        $discountTypeArr = DiscountType::toArrayEnum();
        unset($discountTypeArr[0]);
        unset($discountTypeArr[1]);
        unset($discountTypeArr[4]);
        unset($discountTypeArr[5]);

        $discountType = fake()->randomElement($discountTypeArr);

        $amount = 0;
        if ($discountType == DiscountType::PER_UNIT_SUBTOTAL_PERCENT_DISCOUNT) {
            $amount = mt_rand(1, 99) / 100;
        } elseif ($discountType == DiscountType::PER_UNIT_SUBTOTAL_NOMINAL_DISCOUNT) {
            $amount = fake()->randomFloat(4, 0, 10000000);
        }

        return $this->state(function (array $attributes) use ($discountType, $amount) {
            return [
                'discount_type' => $discountType,
                'amount' => $amount,
            ];
        });
    }
}
