<?php

namespace App\Actions\PurchaseOrderReceiptItem;

use App\Actions\PurchaseOrderReceiptItemSerial\PurchaseOrderReceiptItemSerialActions;
use App\Actions\StockTransaction\StockTransactionActions;
use App\DTOs\PurchaseOrderReceiptItemCreateDTO;
use App\DTOs\PurchaseOrderReceiptItemSerialCreateDTO;
use App\DTOs\PurchaseOrderReceiptItemSerialUpdateDTO;
use App\DTOs\PurchaseOrderReceiptItemUpdateDTO;
use App\DTOs\StockTransactionCreateDTO;
use App\DTOs\StockTransactionUpdateDTO;
use App\Models\ProductUnit;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderReceiptItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;

class PurchaseOrderReceiptItemActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct(
        private readonly StockTransactionActions $stockTransactionActions,
        private readonly PurchaseOrderReceiptItemSerialActions $purchaseOrderReceiptItemSerialActions,
    ) {
    }

    public function create(PurchaseOrderReceiptItemCreateDTO $data): PurchaseOrderReceiptItem
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrderReceiptItem = new PurchaseOrderReceiptItem();
            $purchaseOrderReceiptItem->company_id = $data->companyId;
            $purchaseOrderReceiptItem->branch_id = $data->branchId;
            $purchaseOrderReceiptItem->purchase_order_receipt_id = $data->purchaseOrderReceiptId;
            $purchaseOrderReceiptItem->qty = $data->qty;
            $purchaseOrderReceiptItem->product_unit_id = $data->productUnitId;
            $purchaseOrderReceiptItem->product_id = ProductUnit::query()->whereKey($data->productUnitId)->value('product_id');
            $purchaseOrderReceiptItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $purchaseOrderReceiptItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $purchaseOrderReceiptItem->remarks = $data->remarks;

            $this->applyValuation($purchaseOrderReceiptItem, $data->purchaseOrderItemId);

            $purchaseOrderReceiptItem->save();

            $this->stockTransactionActions->create(
                data: StockTransactionCreateDTO::fromPurchaseOrderReceiptItem($purchaseOrderReceiptItem)
            );

            foreach ($data->serials as $serial) {
                $dto = new PurchaseOrderReceiptItemSerialCreateDTO(
                    companyId: $purchaseOrderReceiptItem->company_id,
                    branchId: $purchaseOrderReceiptItem->branch_id,
                    purchaseOrderReceiptId: $purchaseOrderReceiptItem->purchase_order_receipt_id,
                    purchaseOrderReceiptItemId: $purchaseOrderReceiptItem->id,
                    serial: $serial['serial'],
                );

                $this->purchaseOrderReceiptItemSerialActions->create($dto);
            }

            $this->flushCache();

            return $purchaseOrderReceiptItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseOrderReceiptItem $purchaseOrderReceiptItem, PurchaseOrderReceiptItemUpdateDTO $data): PurchaseOrderReceiptItem
    {
        $timer_start = microtime(true);

        try {
            $purchaseOrderReceiptItem->qty = $data->qty;
            $purchaseOrderReceiptItem->product_unit_id = $data->productUnitId;
            $purchaseOrderReceiptItem->product_id = ProductUnit::query()->whereKey($data->productUnitId)->value('product_id');
            $purchaseOrderReceiptItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $purchaseOrderReceiptItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $purchaseOrderReceiptItem->remarks = $data->remarks;

            $this->applyValuation($purchaseOrderReceiptItem, $data->purchaseOrderItemId);

            $purchaseOrderReceiptItem->save();

            $stockTransaction = $purchaseOrderReceiptItem->stockTransaction;
            if (! $stockTransaction) {
                $dto = StockTransactionCreateDTO::fromPurchaseOrderReceiptItem($purchaseOrderReceiptItem);
                $this->stockTransactionActions->create($dto);
            } else {
                $dto = StockTransactionUpdateDTO::fromPurchaseOrderReceiptItem($purchaseOrderReceiptItem);
                $this->stockTransactionActions->update($stockTransaction, $dto);
            }

            foreach ($data->deleteSerialIds as $deleteId) {
                $purchaseOrderReceiptItemSerial = $purchaseOrderReceiptItem->serials()->findOrFail($deleteId);
                $this->purchaseOrderReceiptItemSerialActions->delete($purchaseOrderReceiptItemSerial);
            }

            foreach ($data->serials as $serial) {
                if ($serial['id']) {
                    $purchaseOrderReceiptItemSerial = $purchaseOrderReceiptItem->serials()->findOrFail($serial['id']);
                    $dto = new PurchaseOrderReceiptItemSerialUpdateDTO(
                        serial: $serial['serial'],
                    );

                    $this->purchaseOrderReceiptItemSerialActions->update($purchaseOrderReceiptItemSerial, $dto);
                } else {
                    $dto = new PurchaseOrderReceiptItemSerialCreateDTO(
                        companyId: $purchaseOrderReceiptItem->company_id,
                        branchId: $purchaseOrderReceiptItem->branch_id,
                        purchaseOrderReceiptId: $purchaseOrderReceiptItem->purchase_order_receipt_id,
                        purchaseOrderReceiptItemId: $purchaseOrderReceiptItem->id,
                        serial: $serial['serial'],
                    );

                    $this->purchaseOrderReceiptItemSerialActions->create($dto);
                }
            }

            $this->flushCache();

            return $purchaseOrderReceiptItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseOrderReceiptItem $purchaseOrderReceiptItem): bool
    {
        $timer_start = microtime(true);

        try {
            $stockTransaction = $purchaseOrderReceiptItem->stockTransaction;
            if ($stockTransaction) {
                $this->stockTransactionActions->delete($stockTransaction);
            }

            foreach ($purchaseOrderReceiptItem->serials as $purchaseOrderReceiptItemSerial) {
                $this->purchaseOrderReceiptItemSerialActions->delete($purchaseOrderReceiptItemSerial);
            }

            $result = $purchaseOrderReceiptItem->delete();

            $this->flushCache();

            return $result;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    /**
     * Snapshot the NET-of-VAT valuation from the matched purchase order item
     * (matched by purchase_order_item_id when given, else by product).
     */
    private function applyValuation(PurchaseOrderReceiptItem $purchaseOrderReceiptItem, ?int $purchaseOrderItemId): void
    {
        $purchaseOrderId = $purchaseOrderReceiptItem->purchaseOrderReceipt()->value('purchase_order_id');

        $purchaseOrderItem = null;

        if ($purchaseOrderItemId) {
            $purchaseOrderItem = PurchaseOrderItem::query()
                ->whereKey($purchaseOrderItemId)
                ->where('purchase_order_id', $purchaseOrderId)
                ->first();
        }

        if (! $purchaseOrderItem) {
            $purchaseOrderItem = PurchaseOrderItem::query()
                ->where('purchase_order_id', $purchaseOrderId)
                ->where('product_id', $purchaseOrderReceiptItem->product_id)
                ->orderBy('id')
                ->first();
        }

        $baseUnitValue = 0.0;
        if ($purchaseOrderItem && (float) $purchaseOrderItem->product_unit_qty_base > 0) {
            $baseUnitValue = ((float) $purchaseOrderItem->amount_payable - (float) $purchaseOrderItem->vat)
                / (float) $purchaseOrderItem->product_unit_qty_base;
        }

        $purchaseOrderReceiptItem->purchase_order_item_id = $purchaseOrderItem?->id;
        $purchaseOrderReceiptItem->has_purchase_order_item_product = ! is_null($purchaseOrderItem);
        $purchaseOrderReceiptItem->base_unit_value = $baseUnitValue;
        $purchaseOrderReceiptItem->total_value = $baseUnitValue * (float) $purchaseOrderReceiptItem->product_unit_qty_base;
    }
}
