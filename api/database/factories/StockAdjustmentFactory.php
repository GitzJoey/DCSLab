<?php

namespace Database\Factories;

use App\Enums\ProductTypeEnum;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentInProduct;
use App\Models\StockAdjustmentOutProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockAdjustment>
 */
class StockAdjustmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->lexify('??')).fake()->numerify('####'),
            'date' => fake()->dateTime(),
            'remarks' => fake()->sentence(),
            'is_posted' => fake()->boolean(),
            'total_incoming_product_qty' => 0,
            'total_incoming_product_cogs' => 0,
            'total_outgoing_product_qty' => 0,
        ];
    }

    public function withInProducts(int $count = 1): static
    {
        return $this->afterCreating(function (StockAdjustment $stockAdjustment) use ($count) {
            $inProducts = collect();
            for ($i = 0; $i < $count; $i++) {
                $productUnit = $this->getProductUnit($stockAdjustment->company_id);

                $inProducts->push(StockAdjustmentInProduct::factory()->create([
                    'stock_adjustment_id' => $stockAdjustment->id,
                    'company_id' => $stockAdjustment->company_id,
                    'branch_id' => $stockAdjustment->branch_id,
                    'product_unit_id' => $productUnit->id,
                ]));
            }

            $stockAdjustment->update([
                'total_incoming_product_qty' => $stockAdjustment->total_incoming_product_qty + $inProducts->sum('qty'),
                'total_incoming_product_cogs' => $stockAdjustment->total_incoming_product_cogs + $inProducts->sum('product_unit_total_cogs'),
            ]);
        });
    }

    public function withOutProducts(int $count = 1): static
    {
        return $this->afterCreating(function (StockAdjustment $stockAdjustment) use ($count) {
            $outProducts = collect();
            for ($i = 0; $i < $count; $i++) {
                $product = $this->getProduct($stockAdjustment->company_id);

                $outProducts->push(StockAdjustmentOutProduct::factory()->create([
                    'stock_adjustment_id' => $stockAdjustment->id,
                    'company_id' => $stockAdjustment->company_id,
                    'branch_id' => $stockAdjustment->branch_id,
                    'product_id' => $product->id,
                ]));
            }

            $stockAdjustment->update([
                'total_outgoing_product_qty' => $stockAdjustment->total_outgoing_product_qty + $outProducts->sum('qty'),
            ]);
        });
    }

    private function getProductUnit(int $companyId): ProductUnit
    {
        $product = $this->getProduct($companyId);

        $productUnit = $product->productUnits()->inRandomOrder()->first();

        if ($productUnit) {
            return $productUnit;
        }

        return ProductUnit::factory()->create([
            'company_id' => $companyId,
            'product_id' => $product->id,
        ]);
    }

    private function getProduct(int $companyId): Product
    {
        $query = Product::where('company_id', $companyId)
            ->whereIn('type', [
                ProductTypeEnum::RAW_MATERIAL,
                ProductTypeEnum::WORK_IN_PROGRESS,
                ProductTypeEnum::FINISHED_GOODS,
            ]);

        if ($query->exists()) {
            return $query->inRandomOrder()->first();
        }

        $type = fake()->randomElement(['rawMaterial', 'workInProgress', 'finishedGoods']);

        return Product::factory()->{$type}()->create([
            'company_id' => $companyId,
        ]);
    }
}
