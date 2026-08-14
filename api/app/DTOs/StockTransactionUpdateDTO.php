<?php

namespace App\DTOs;

use App\Models\PurchaseOrderReceiptItem;
use App\Models\PurchaseReturnItem;
use App\Models\SalesOrderDeliveryItem;
use App\Models\SalesReturnItem;
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

    public static function fromPurchaseOrderReceiptItem(PurchaseOrderReceiptItem $purchaseOrderReceiptItem): self
    {
        return new self(
            referableType: PurchaseOrderReceiptItem::class,
            referableId: $purchaseOrderReceiptItem->id,
            date: $purchaseOrderReceiptItem->purchaseOrderReceipt->date,
            warehouseId: $purchaseOrderReceiptItem->purchaseOrderReceipt->warehouse_id,
            productId: $purchaseOrderReceiptItem->productUnit->product_id,
            baseQty: $purchaseOrderReceiptItem->product_unit_qty_base,
        );
    }

    public static function fromPurchaseReturnItem(PurchaseReturnItem $purchaseReturnItem): self
    {
        return new self(
            referableType: PurchaseReturnItem::class,
            referableId: $purchaseReturnItem->id,
            date: $purchaseReturnItem->purchaseReturn->date,
            warehouseId: $purchaseReturnItem->purchaseReturn->warehouse_id,
            productId: $purchaseReturnItem->productUnit->product_id,
            baseQty: $purchaseReturnItem->product_unit_qty_base * -1,
        );
    }

    public static function fromSalesOrderDeliveryItem(SalesOrderDeliveryItem $salesOrderDeliveryItem): self
    {
        return new self(
            referableType: SalesOrderDeliveryItem::class,
            referableId: $salesOrderDeliveryItem->id,
            date: $salesOrderDeliveryItem->salesOrderDelivery->date,
            warehouseId: $salesOrderDeliveryItem->salesOrderDelivery->warehouse_id,
            productId: $salesOrderDeliveryItem->productUnit->product_id,
            baseQty: $salesOrderDeliveryItem->product_unit_qty_base * -1,
        );
    }

    public static function fromSalesReturnItem(SalesReturnItem $salesReturnItem): self
    {
        return new self(
            referableType: SalesReturnItem::class,
            referableId: $salesReturnItem->id,
            date: $salesReturnItem->salesReturn->date,
            warehouseId: $salesReturnItem->salesReturn->warehouse_id,
            productId: $salesReturnItem->productUnit->product_id,
            baseQty: $salesReturnItem->product_unit_qty_base,
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
