<?php

namespace App\DTOs;

use App\Models\StockAdjustmentInProduct;
use App\Models\StockAdjustmentOutProduct;
use App\Models\StockTransferProductUnit;

final class StockTransactionUpdateDTO
{
    public function __construct(
        public readonly string $referableType,
        public readonly int $referableId,
        public readonly string $date,
        public readonly int $warehouseId,
        public readonly int $productId,
        public readonly float $baseQty,
    ) {
    }

    public static function fromStockAdjustmentInProduct(StockAdjustmentInProduct $stockAdjustmentInProduct): self
    {
        return new self(
            referableType: StockAdjustmentInProduct::class,
            referableId: $stockAdjustmentInProduct->id,
            date: $stockAdjustmentInProduct->stockAdjustment->date,
            warehouseId: $stockAdjustmentInProduct->stockAdjustment->in_warehouse_id,
            productId: $stockAdjustmentInProduct->productUnit->product_id,
            baseQty: $stockAdjustmentInProduct->product_unit_qty_base,
        );
    }

    public static function fromStockAdjustmentOutProduct(StockAdjustmentOutProduct $stockAdjustmentOutProduct): self
    {
        return new self(
            referableType: StockAdjustmentOutProduct::class,
            referableId: $stockAdjustmentOutProduct->id,
            date: $stockAdjustmentOutProduct->stockAdjustment->date,
            warehouseId: $stockAdjustmentOutProduct->stockAdjustment->out_warehouse_id,
            productId: $stockAdjustmentOutProduct->productUnit->product_id,
            baseQty: $stockAdjustmentOutProduct->product_unit_qty_base * -1,
        );
    }

    public static function fromStockTransferProductUnitSource(StockTransferProductUnit $stockTransferProductUnit): self
    {
        return new self(
            referableType: StockTransferProductUnit::class,
            referableId: $stockTransferProductUnit->id,
            date: $stockTransferProductUnit->stockTransfer->date,
            warehouseId: $stockTransferProductUnit->stockTransfer->source_warehouse_id,
            productId: $stockTransferProductUnit->productUnit->product_id,
            baseQty: $stockTransferProductUnit->product_unit_qty_base * -1,
        );
    }

    public static function fromStockTransferProductUnitDestination(StockTransferProductUnit $stockTransferProductUnit): self
    {
        return new self(
            referableType: StockTransferProductUnit::class,
            referableId: $stockTransferProductUnit->id,
            date: $stockTransferProductUnit->stockTransfer->date,
            warehouseId: $stockTransferProductUnit->stockTransfer->destination_warehouse_id,
            productId: $stockTransferProductUnit->productUnit->product_id,
            baseQty: $stockTransferProductUnit->product_unit_qty_base,
        );
    }
}
