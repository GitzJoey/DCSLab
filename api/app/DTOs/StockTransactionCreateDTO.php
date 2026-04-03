<?php

namespace App\DTOs;

use App\Models\StockAdjustmentInProduct;
use App\Models\StockAdjustmentOutProduct;
use App\Models\StockTransferItem;

final class StockTransactionCreateDTO
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

    public static function fromStockTransferItemSource(StockTransferItem $stockTransferItem): self
    {
        return new self(
            referableType: StockTransferItem::class,
            referableId: $stockTransferItem->id,
            date: $stockTransferItem->stockTransfer->date,
            warehouseId: $stockTransferItem->stockTransfer->source_warehouse_id,
            productId: $stockTransferItem->productUnit->product_id,
            baseQty: $stockTransferItem->product_unit_qty_base * -1,
        );
    }

    public static function fromStockTransferItemDestination(StockTransferItem $stockTransferItem): self
    {
        return new self(
            referableType: StockTransferItem::class,
            referableId: $stockTransferItem->id,
            date: $stockTransferItem->stockTransfer->date,
            warehouseId: $stockTransferItem->stockTransfer->destination_warehouse_id,
            productId: $stockTransferItem->productUnit->product_id,
            baseQty: $stockTransferItem->product_unit_qty_base,
        );
    }
}
