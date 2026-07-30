<?php

namespace App\Actions\SalesReturnItem;

use App\Actions\SalesReturn\SalesReturnActions;
use App\Actions\SalesReturnItemSerial\SalesReturnItemSerialActions;
use App\Actions\StockTransaction\StockTransactionActions;
use App\DTOs\SalesReturnItemCreateDTO;
use App\DTOs\SalesReturnItemSerialCreateDTO;
use App\DTOs\SalesReturnItemSerialUpdateDTO;
use App\DTOs\SalesReturnItemUpdateDTO;
use App\DTOs\StockTransactionCreateDTO;
use App\DTOs\StockTransactionUpdateDTO;
use App\Models\ProductUnit;
use App\Models\PurchaseInvoiceItem;
use App\Models\PurchaseOrderItem;
use App\Models\SalesOrderDeliveryItem;
use App\Models\SalesReturnItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;

class SalesReturnItemActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct(
        private readonly SalesReturnItemSerialActions $salesReturnItemSerialActions,
        private readonly StockTransactionActions $stockTransactionActions,
    ) {
    }

    public function create(
        SalesReturnItemCreateDTO $data,
        bool $updateParentSummary,
    ): SalesReturnItem {
        $timer_start = microtime(true);

        try {
            $salesReturnItem = new SalesReturnItem();
            $salesReturnItem->company_id = $data->companyId;
            $salesReturnItem->branch_id = $data->branchId;
            $salesReturnItem->sales_return_id = $data->salesReturnId;
            $salesReturnItem->sales_order_delivery_item_id = $data->salesOrderDeliveryItemId;
            $salesReturnItem->qty = $data->qty;
            $salesReturnItem->product_unit_id = $data->productUnitId;
            $salesReturnItem->product_id = ProductUnit::query()->whereKey($data->productUnitId)->value('product_id');
            $salesReturnItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $salesReturnItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $salesReturnItem->product_unit_price = $data->productUnitPrice;
            $salesReturnItem->product_unit_is_price_include_vat = $data->productUnitIsPriceIncludeVat;
            $salesReturnItem->vat_profile_id = $data->vatProfileId;
            $salesReturnItem->vat_rate = $data->vatRate;
            $salesReturnItem->vat_base_numerator = $data->vatBaseNumerator;
            $salesReturnItem->vat_base_denominator = $data->vatBaseDenominator;
            $salesReturnItem->remarks = $data->remarks;

            $salesReturnItem->price_discount = min(max((float) $data->priceDiscount, 0), (float) $data->productUnitPrice);
            $salesReturnItem->price_after_discount = (float) $salesReturnItem->product_unit_price - (float) $salesReturnItem->price_discount;
            $salesReturnItem->subtotal = (float) $salesReturnItem->qty * (float) $salesReturnItem->price_after_discount;
            $salesReturnItem->subtotal_discount = min(max((float) $data->subtotalDiscount, 0), (float) $salesReturnItem->subtotal);
            $salesReturnItem->subtotal_after_discount = (float) $salesReturnItem->subtotal - (float) $salesReturnItem->subtotal_discount;

            $salesReturnItem->base_unit_cogs = $this->resolveBaseUnitCogs(
                companyId: $data->companyId,
                productId: (int) $salesReturnItem->product_id,
                salesOrderDeliveryItemId: $data->salesOrderDeliveryItemId,
            );
            $salesReturnItem->total_cogs = (float) $salesReturnItem->base_unit_cogs * (float) $salesReturnItem->product_unit_qty_base;
            $salesReturnItem->save();

            $this->stockTransactionActions->create(
                data: StockTransactionCreateDTO::fromSalesReturnItem($salesReturnItem)
            );

            foreach ($data->serials as $serial) {
                $dto = new SalesReturnItemSerialCreateDTO(
                    companyId: $salesReturnItem->company_id,
                    branchId: $salesReturnItem->branch_id,
                    salesReturnId: $salesReturnItem->sales_return_id,
                    salesReturnItemId: $salesReturnItem->id,
                    serial: $serial['serial'],
                );

                $this->salesReturnItemSerialActions->create($dto);
            }

            if ($updateParentSummary) {
                SalesReturnActions::updateSummary($salesReturnItem->salesReturn);
                $salesReturnItem->refresh();
            }

            $this->flushCache();

            return $salesReturnItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(
        SalesReturnItem $salesReturnItem,
        SalesReturnItemUpdateDTO $data,
        bool $updateParentSummary,
    ): SalesReturnItem {
        $timer_start = microtime(true);

        try {
            $salesReturnItem->sales_order_delivery_item_id = $data->salesOrderDeliveryItemId;
            $salesReturnItem->qty = $data->qty;
            $salesReturnItem->product_unit_id = $data->productUnitId;
            $salesReturnItem->product_id = ProductUnit::query()->whereKey($data->productUnitId)->value('product_id');
            $salesReturnItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $salesReturnItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $salesReturnItem->product_unit_price = $data->productUnitPrice;
            $salesReturnItem->product_unit_is_price_include_vat = $data->productUnitIsPriceIncludeVat;
            $salesReturnItem->vat_profile_id = $data->vatProfileId;
            $salesReturnItem->vat_rate = $data->vatRate;
            $salesReturnItem->vat_base_numerator = $data->vatBaseNumerator;
            $salesReturnItem->vat_base_denominator = $data->vatBaseDenominator;
            $salesReturnItem->remarks = $data->remarks;

            $salesReturnItem->price_discount = min(max((float) $data->priceDiscount, 0), (float) $data->productUnitPrice);
            $salesReturnItem->price_after_discount = (float) $salesReturnItem->product_unit_price - (float) $salesReturnItem->price_discount;
            $salesReturnItem->subtotal = (float) $salesReturnItem->qty * (float) $salesReturnItem->price_after_discount;
            $salesReturnItem->subtotal_discount = min(max((float) $data->subtotalDiscount, 0), (float) $salesReturnItem->subtotal);
            $salesReturnItem->subtotal_after_discount = (float) $salesReturnItem->subtotal - (float) $salesReturnItem->subtotal_discount;

            $salesReturnItem->base_unit_cogs = $this->resolveBaseUnitCogs(
                companyId: $salesReturnItem->company_id,
                productId: (int) $salesReturnItem->product_id,
                salesOrderDeliveryItemId: $data->salesOrderDeliveryItemId,
            );
            $salesReturnItem->total_cogs = (float) $salesReturnItem->base_unit_cogs * (float) $salesReturnItem->product_unit_qty_base;
            $salesReturnItem->save();

            $stockTransaction = $salesReturnItem->stockTransaction;
            if (! $stockTransaction) {
                $this->stockTransactionActions->create(
                    data: StockTransactionCreateDTO::fromSalesReturnItem($salesReturnItem)
                );
            } else {
                $this->stockTransactionActions->update(
                    stockTransaction: $stockTransaction,
                    data: StockTransactionUpdateDTO::fromSalesReturnItem($salesReturnItem)
                );
            }

            foreach ($data->deleteSerialIds as $deleteId) {
                $salesReturnItemSerial = $salesReturnItem->itemSerials()->findOrFail($deleteId);
                $this->salesReturnItemSerialActions->delete($salesReturnItemSerial);
            }

            foreach ($data->serials as $serial) {
                if (! empty($serial['id'])) {
                    $salesReturnItemSerial = $salesReturnItem->itemSerials()->findOrFail($serial['id']);
                    $dto = new SalesReturnItemSerialUpdateDTO(
                        serial: $serial['serial'],
                    );

                    $this->salesReturnItemSerialActions->update($salesReturnItemSerial, $dto);
                } else {
                    $dto = new SalesReturnItemSerialCreateDTO(
                        companyId: $salesReturnItem->company_id,
                        branchId: $salesReturnItem->branch_id,
                        salesReturnId: $salesReturnItem->sales_return_id,
                        salesReturnItemId: $salesReturnItem->id,
                        serial: $serial['serial'],
                    );

                    $this->salesReturnItemSerialActions->create($dto);
                }
            }

            if ($updateParentSummary) {
                SalesReturnActions::updateSummary($salesReturnItem->salesReturn);
                $salesReturnItem->refresh();
            }

            $this->flushCache();

            return $salesReturnItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SalesReturnItem $salesReturnItem, bool $updateParentSummary): bool
    {
        $timer_start = microtime(true);

        try {
            $salesReturn = $salesReturnItem->salesReturn;

            $stockTransaction = $salesReturnItem->stockTransaction;
            if ($stockTransaction) {
                $this->stockTransactionActions->delete($stockTransaction);
            }

            foreach ($salesReturnItem->itemSerials as $salesReturnItemSerial) {
                $this->salesReturnItemSerialActions->delete($salesReturnItemSerial);
            }

            $result = $salesReturnItem->delete();

            if ($updateParentSummary) {
                SalesReturnActions::updateSummary($salesReturn);
            }

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
     * Cost snapshot resolver (design §1.5): when the return line is linked to a
     * delivery line, COPY the exact base_unit_cogs that the delivery posted;
     * otherwise use the latest known NET purchase cost for the product.
     */
    private function resolveBaseUnitCogs(int $companyId, int $productId, ?int $salesOrderDeliveryItemId): float
    {
        if ($salesOrderDeliveryItemId) {
            $deliveryItemBaseUnitCogs = SalesOrderDeliveryItem::query()
                ->whereKey($salesOrderDeliveryItemId)
                ->value('base_unit_cogs');

            return is_null($deliveryItemBaseUnitCogs) ? 0.0 : (float) $deliveryItemBaseUnitCogs;
        }

        return $this->resolveLatestBaseUnitCogs($companyId, $productId);
    }

    /**
     * Latest-cost resolver (same as the sales delivery one, design §1.5):
     * most recent purchase invoice item cost, fallback most recent purchase
     * order item cost, fallback 0.
     */
    private function resolveLatestBaseUnitCogs(int $companyId, int $productId): float
    {
        $invoiceBaseUnitCogs = PurchaseInvoiceItem::query()
            ->join('purchase_invoices', 'purchase_invoices.id', '=', 'purchase_invoice_items.purchase_invoice_id')
            ->where('purchase_invoice_items.company_id', $companyId)
            ->where('purchase_invoice_items.product_id', $productId)
            ->whereNull('purchase_invoices.deleted_at')
            ->orderBy('purchase_invoices.date', 'desc')
            ->orderBy('purchase_invoice_items.id', 'desc')
            ->value('purchase_invoice_items.base_unit_cogs');

        if (! is_null($invoiceBaseUnitCogs)) {
            return (float) $invoiceBaseUnitCogs;
        }

        $purchaseOrderBaseUnitCogs = PurchaseOrderItem::query()
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->where('purchase_order_items.company_id', $companyId)
            ->where('purchase_order_items.product_id', $productId)
            ->whereNull('purchase_orders.deleted_at')
            ->orderBy('purchase_orders.date', 'desc')
            ->orderBy('purchase_order_items.id', 'desc')
            ->value('purchase_order_items.base_unit_cogs');

        return is_null($purchaseOrderBaseUnitCogs) ? 0.0 : (float) $purchaseOrderBaseUnitCogs;
    }
}
