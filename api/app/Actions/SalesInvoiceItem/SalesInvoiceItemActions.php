<?php

namespace App\Actions\SalesInvoiceItem;

use App\Actions\SalesInvoice\SalesInvoiceActions;
use App\DTOs\SalesInvoiceItemCreateDTO;
use App\DTOs\SalesInvoiceItemUpdateDTO;
use App\Models\ProductUnit;
use App\Models\SalesInvoiceItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;

class SalesInvoiceItemActions
{
    use CacheHelper;
    use LoggerHelper;

    public function create(SalesInvoiceItemCreateDTO $data, bool $updateParentSummary): SalesInvoiceItem
    {
        $timer_start = microtime(true);

        try {
            $salesInvoiceItem = new SalesInvoiceItem();
            $salesInvoiceItem->company_id = $data->companyId;
            $salesInvoiceItem->branch_id = $data->branchId;
            $salesInvoiceItem->sales_invoice_id = $data->salesInvoiceId;
            $salesInvoiceItem->sales_order_item_id = $data->salesOrderItemId;
            $salesInvoiceItem->qty = $data->qty;
            $salesInvoiceItem->product_unit_id = $data->productUnitId;
            $salesInvoiceItem->product_id = ProductUnit::query()->whereKey($data->productUnitId)->value('product_id');
            $salesInvoiceItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $salesInvoiceItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $salesInvoiceItem->product_unit_price = $data->productUnitPrice;
            $salesInvoiceItem->product_unit_is_price_include_vat = $data->productUnitIsPriceIncludeVat;
            $salesInvoiceItem->vat_profile_id = $data->vatProfileId;
            $salesInvoiceItem->vat_rate = $data->vatRate;
            $salesInvoiceItem->vat_base_numerator = $data->vatBaseNumerator;
            $salesInvoiceItem->vat_base_denominator = $data->vatBaseDenominator;
            $salesInvoiceItem->remarks = $data->remarks;

            $this->calculateItemPricing($salesInvoiceItem, $data->priceDiscount, $data->subtotalDiscount);

            $salesInvoiceItem->save();

            if ($updateParentSummary) {
                SalesInvoiceActions::updateSummary($salesInvoiceItem->salesInvoice);
                $salesInvoiceItem->refresh();
            }

            $this->flushCache();

            return $salesInvoiceItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(SalesInvoiceItem $salesInvoiceItem, SalesInvoiceItemUpdateDTO $data, bool $updateParentSummary): SalesInvoiceItem
    {
        $timer_start = microtime(true);

        try {
            $salesInvoiceItem->sales_order_item_id = $data->salesOrderItemId;
            $salesInvoiceItem->qty = $data->qty;
            $salesInvoiceItem->product_unit_id = $data->productUnitId;
            $salesInvoiceItem->product_id = ProductUnit::query()->whereKey($data->productUnitId)->value('product_id');
            $salesInvoiceItem->product_unit_conversion_value = $data->productUnitConversionValue;
            $salesInvoiceItem->product_unit_qty_base = $data->qty * $data->productUnitConversionValue;
            $salesInvoiceItem->product_unit_price = $data->productUnitPrice;
            $salesInvoiceItem->product_unit_is_price_include_vat = $data->productUnitIsPriceIncludeVat;
            $salesInvoiceItem->vat_profile_id = $data->vatProfileId;
            $salesInvoiceItem->vat_rate = $data->vatRate;
            $salesInvoiceItem->vat_base_numerator = $data->vatBaseNumerator;
            $salesInvoiceItem->vat_base_denominator = $data->vatBaseDenominator;
            $salesInvoiceItem->remarks = $data->remarks;

            $this->calculateItemPricing($salesInvoiceItem, $data->priceDiscount, $data->subtotalDiscount);

            $salesInvoiceItem->save();

            if ($updateParentSummary) {
                SalesInvoiceActions::updateSummary($salesInvoiceItem->salesInvoice);
                $salesInvoiceItem->refresh();
            }

            $this->flushCache();

            return $salesInvoiceItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(SalesInvoiceItem $salesInvoiceItem, bool $updateParentSummary): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $salesInvoice = $salesInvoiceItem->salesInvoice;

            $retval = $salesInvoiceItem->delete();

            if ($updateParentSummary) {
                SalesInvoiceActions::updateSummary($salesInvoice);
            }

            $this->flushCache();

            return $retval;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    /**
     * Single nominal discounts replicate the legacy discount fold behavior:
     * the effective discount is capped at the amount being discounted (floor 0).
     */
    private function calculateItemPricing(SalesInvoiceItem $salesInvoiceItem, float $priceDiscount, float $subtotalDiscount): void
    {
        $salesInvoiceItem->price_discount = min(max(0, $priceDiscount), (float) $salesInvoiceItem->product_unit_price);
        $salesInvoiceItem->price_after_discount = (float) $salesInvoiceItem->product_unit_price - (float) $salesInvoiceItem->price_discount;
        $salesInvoiceItem->subtotal = (float) $salesInvoiceItem->qty * (float) $salesInvoiceItem->price_after_discount;
        $salesInvoiceItem->subtotal_discount = min(max(0, $subtotalDiscount), (float) $salesInvoiceItem->subtotal);
        $salesInvoiceItem->subtotal_after_discount = (float) $salesInvoiceItem->subtotal - (float) $salesInvoiceItem->subtotal_discount;
    }
}
