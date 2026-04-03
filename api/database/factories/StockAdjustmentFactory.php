<?php

namespace Database\Factories;

use App\Enums\ProductTypeEnum;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentInItem;
use App\Models\StockAdjustmentOutItem;
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
            'total_incoming_item_qty' => 0,
            'total_incoming_item_cogs' => 0,
            'total_outgoing_item_qty' => 0,
        ];
    }

    public function withInProducts(int $count = 1): static
    {
        return $this->afterCreating(function (StockAdjustment $stockAdjustment) use ($count) {
            $inItems = collect();
            for ($i = 0; $i < $count; $i++) {
                $productUnit = $this->getProductUnit($stockAdjustment->company_id);

                $inItems->push(StockAdjustmentInItem::factory()->create([
                    'stock_adjustment_id' => $stockAdjustment->id,
                    'company_id' => $stockAdjustment->company_id,
                    'branch_id' => $stockAdjustment->branch_id,
                    'product_unit_id' => $productUnit->id,
                ]));
            }

            $stockAdjustment->update([
                'total_incoming_item_qty' => $stockAdjustment->total_incoming_item_qty + $inItems->sum('qty'),
                'total_incoming_item_cogs' => $stockAdjustment->total_incoming_item_cogs + $inItems->sum('product_unit_total_cogs'),
            ]);
        });
    }

    public function withOutProducts(int $count = 1): static
    {
        return $this->afterCreating(function (StockAdjustment $stockAdjustment) use ($count) {
            $outItems = collect();
            for ($i = 0; $i < $count; $i++) {
                $product = $this->getProduct($stockAdjustment->company_id);

                $outItems->push(StockAdjustmentOutItem::factory()->create([
                    'stock_adjustment_id' => $stockAdjustment->id,
                    'company_id' => $stockAdjustment->company_id,
                    'branch_id' => $stockAdjustment->branch_id,
                    'product_id' => $product->id,
                ]));
            }

            $stockAdjustment->update([
                'total_outgoing_item_qty' => $stockAdjustment->total_outgoing_item_qty + $outItems->sum('qty'),
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
