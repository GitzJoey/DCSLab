<?php

namespace App\DTOs;

use App\Models\PurchaseOrderReceiptItemSerial;
use App\Models\PurchaseReturnItemSerial;
use App\Models\SalesOrderDeliveryItemSerial;
use App\Models\SalesReturnItemSerial;
use App\Models\StockAdjustmentInItemSerial;
use App\Models\StockAdjustmentOutItemSerial;
use App\Models\StockTransferItemSerial;

final class StockSerialTransactionUpdateDTO
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

    public static function fromStockAdjustmentInItemSerial(StockAdjustmentInItemSerial $stockAdjustmentInItemSerial, string $serial): self
    {
        return new self(
            referableType: StockAdjustmentInItemSerial::class,
            referableId: $stockAdjustmentInItemSerial->id,
            date: $stockAdjustmentInItemSerial->stockAdjustmentInItem->stockAdjustment->date,
            warehouseId: $stockAdjustmentInItemSerial->stockAdjustmentInItem->stockAdjustment->in_warehouse_id,
            productId: $stockAdjustmentInItemSerial->stockAdjustmentInItem->productUnit->product_id,
            direction: 1,
            serial: $serial,
        );
    }

    public static function fromStockAdjustmentOutItemSerial(StockAdjustmentOutItemSerial $stockAdjustmentOutItemSerial, string $serial): self
    {
        return new self(
            referableType: StockAdjustmentOutItemSerial::class,
            referableId: $stockAdjustmentOutItemSerial->id,
            date: $stockAdjustmentOutItemSerial->stockAdjustmentOutItem->stockAdjustment->date,
            warehouseId: $stockAdjustmentOutItemSerial->stockAdjustmentOutItem->stockAdjustment->out_warehouse_id,
            productId: $stockAdjustmentOutItemSerial->stockAdjustmentOutItem->productUnit->product_id,
            direction: -1,
            serial: $serial,
        );
    }

    public static function fromPurchaseOrderReceiptItemSerial(PurchaseOrderReceiptItemSerial $purchaseOrderReceiptItemSerial, string $serial): self
    {
        return new self(
            referableType: PurchaseOrderReceiptItemSerial::class,
            referableId: $purchaseOrderReceiptItemSerial->id,
            date: $purchaseOrderReceiptItemSerial->purchaseOrderReceipt->date,
            warehouseId: $purchaseOrderReceiptItemSerial->purchaseOrderReceipt->warehouse_id,
            productId: $purchaseOrderReceiptItemSerial->purchaseOrderReceiptItem->productUnit->product_id,
            direction: 1,
            serial: $serial,
        );
    }

    public static function fromPurchaseReturnItemSerial(PurchaseReturnItemSerial $purchaseReturnItemSerial, string $serial): self
    {
        return new self(
            referableType: PurchaseReturnItemSerial::class,
            referableId: $purchaseReturnItemSerial->id,
            date: $purchaseReturnItemSerial->purchaseReturn->date,
            warehouseId: $purchaseReturnItemSerial->purchaseReturn->warehouse_id,
            productId: $purchaseReturnItemSerial->purchaseReturnItem->productUnit->product_id,
            direction: -1,
            serial: $serial,
        );
    }

    public static function fromSalesOrderDeliveryItemSerial(SalesOrderDeliveryItemSerial $salesOrderDeliveryItemSerial, string $serial): self
    {
        return new self(
            referableType: SalesOrderDeliveryItemSerial::class,
            referableId: $salesOrderDeliveryItemSerial->id,
            date: $salesOrderDeliveryItemSerial->salesOrderDelivery->date,
            warehouseId: $salesOrderDeliveryItemSerial->salesOrderDelivery->warehouse_id,
            productId: $salesOrderDeliveryItemSerial->salesOrderDeliveryItem->productUnit->product_id,
            direction: -1,
            serial: $serial,
        );
    }

    public static function fromSalesReturnItemSerial(SalesReturnItemSerial $salesReturnItemSerial, string $serial): self
    {
        return new self(
            referableType: SalesReturnItemSerial::class,
            referableId: $salesReturnItemSerial->id,
            date: $salesReturnItemSerial->salesReturn->date,
            warehouseId: $salesReturnItemSerial->salesReturn->warehouse_id,
            productId: $salesReturnItemSerial->salesReturnItem->productUnit->product_id,
            direction: 1,
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
