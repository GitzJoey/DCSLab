<?php

namespace App\DTOs;

use App\Models\StockAdjustmentInProductSerial;
use App\Models\StockAdjustmentOutProductSerial;
use App\Models\StockTransferItemSerial;

final class StockSerialTransactionCreateDTO
{
    public function __construct(
        public readonly string $referableType,
        public readonly int $referableId,
        public readonly string $date,
        public readonly int $warehouseId,
        public readonly int $productId,
        public readonly int $direction,
        public readonly string $serial,
    ) {
    }

    public static function fromStockAdjustmentInProductSerial(StockAdjustmentInProductSerial $stockAdjustmentInProductSerial, string $serial): self
    {
        return new self(
            referableType: StockAdjustmentInProductSerial::class,
            referableId: $stockAdjustmentInProductSerial->id,
            date: $stockAdjustmentInProductSerial->stockAdjustmentInProduct->stockAdjustment->date,
            warehouseId: $stockAdjustmentInProductSerial->stockAdjustmentInProduct->stockAdjustment->in_warehouse_id,
            productId: $stockAdjustmentInProductSerial->stockAdjustmentInProduct->productUnit->product_id,
            direction: 1,
            serial: $serial,
        );
    }

    public static function fromStockAdjustmentOutProductSerial(StockAdjustmentOutProductSerial $stockAdjustmentOutProductSerial, string $serial): self
    {
        return new self(
            referableType: StockAdjustmentOutProductSerial::class,
            referableId: $stockAdjustmentOutProductSerial->id,
            date: $stockAdjustmentOutProductSerial->stockAdjustmentOutProduct->stockAdjustment->date,
            warehouseId: $stockAdjustmentOutProductSerial->stockAdjustmentOutProduct->stockAdjustment->out_warehouse_id,
            productId: $stockAdjustmentOutProductSerial->stockAdjustmentOutProduct->productUnit->product_id,
            direction: -1,
            serial: $serial,
        );
    }

    public static function fromStockTransferItemSerialSource(StockTransferItemSerial $stockTransferItemSerial, string $serial): self
    {
        return new self(
            referableType: StockTransferItemSerial::class,
            referableId: $stockTransferItemSerial->id,
            date: $stockTransferItemSerial->stockTransferItem->stockTransfer->date,
            warehouseId: $stockTransferItemSerial->stockTransferItem->stockTransfer->source_warehouse_id,
            productId: $stockTransferItemSerial->stockTransferItem->productUnit->product_id,
            direction: -1,
            serial: $serial,
        );
    }

    public static function fromStockTransferItemSerialDestination(StockTransferItemSerial $stockTransferItemSerial, string $serial): self
    {
        return new self(
            referableType: StockTransferItemSerial::class,
            referableId: $stockTransferItemSerial->id,
            date: $stockTransferItemSerial->stockTransferItem->stockTransfer->date,
            warehouseId: $stockTransferItemSerial->stockTransferItem->stockTransfer->destination_warehouse_id,
            productId: $stockTransferItemSerial->stockTransferItem->productUnit->product_id,
            direction: 1,
            serial: $serial,
        );
    }
}
