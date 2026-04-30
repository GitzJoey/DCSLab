<?php

namespace App\DTOs;

use App\Models\PurchaseReceiptItem;
use App\Models\StockAdjustmentInItem;
use App\Models\StockAdjustmentOutItem;
use App\Models\StockTransferItem;

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

    public static function fromStockAdjustmentInItem(StockAdjustmentInItem $stockAdjustmentInItem): self
    {
        return new self(
            referableType: StockAdjustmentInItem::class,
            referableId: $stockAdjustmentInItem->id,
            date: $stockAdjustmentInItem->stockAdjustment->date,
            warehouseId: $stockAdjustmentInItem->stockAdjustment->in_warehouse_id,
            productId: $stockAdjustmentInItem->productUnit->product_id,
            baseQty: $stockAdjustmentInItem->product_unit_qty_base,
        );
    }

    public static function fromStockAdjustmentOutItem(StockAdjustmentOutItem $stockAdjustmentOutItem): self
    {
        return new self(
            referableType: StockAdjustmentOutItem::class,
            referableId: $stockAdjustmentOutItem->id,
            date: $stockAdjustmentOutItem->stockAdjustment->date,
            warehouseId: $stockAdjustmentOutItem->stockAdjustment->out_warehouse_id,
            productId: $stockAdjustmentOutItem->productUnit->product_id,
            baseQty: $stockAdjustmentOutItem->product_unit_qty_base * -1,
        );
    }

    public static function fromPurchaseReceiptItem(PurchaseReceiptItem $purchaseReceiptItem): self
    {
        return new self(
            referableType: PurchaseReceiptItem::class,
            referableId: $purchaseReceiptItem->id,
            date: $purchaseReceiptItem->purchaseReceipt->date,
            warehouseId: $purchaseReceiptItem->purchaseReceipt->warehouse_id,
            productId: $purchaseReceiptItem->productUnit->product_id,
            baseQty: $purchaseReceiptItem->product_unit_qty_base,
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
