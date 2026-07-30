<?php

namespace App\Actions\PurchaseInvoiceItem;

use App\Actions\PurchaseInvoice\PurchaseInvoiceActions;
use App\DTOs\PurchaseInvoiceItemCreateDTO;
use App\DTOs\PurchaseInvoiceItemUpdateDTO;
use App\Models\ProductUnit;
use App\Models\PurchaseInvoiceItem;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;

class PurchaseInvoiceItemActions
{
    use CacheHelper;
    use LoggerHelper;

    public function create(PurchaseInvoiceItemCreateDTO $data, bool $updateParentSummary = true): PurchaseInvoiceItem
    {
        $timer_start = microtime(true);

        try {
            $purchaseInvoiceItem = new PurchaseInvoiceItem();
            $purchaseInvoiceItem->company_id = $data->companyId;
            $purchaseInvoiceItem->branch_id = $data->branchId;
            $purchaseInvoiceItem->purchase_invoice_id = $data->purchaseInvoiceId;
            $purchaseInvoiceItem->purchase_order_item_id = $data->purchaseOrderItemId;

            $this->fillCalculatedColumns($purchaseInvoiceItem, [
                'qty' => $data->qty,
                'productUnitId' => $data->productUnitId,
                'productUnitConversionValue' => $data->productUnitConversionValue,
                'productUnitPrice' => $data->productUnitPrice,
                'productUnitIsPriceIncludeVat' => $data->productUnitIsPriceIncludeVat,
                'priceDiscount' => $data->priceDiscount,
                'subtotalDiscount' => $data->subtotalDiscount,
                'vatProfileId' => $data->vatProfileId,
                'vatRate' => $data->vatRate,
                'vatBaseNumerator' => $data->vatBaseNumerator,
                'vatBaseDenominator' => $data->vatBaseDenominator,
                'remarks' => $data->remarks,
            ]);

            $purchaseInvoiceItem->save();

            if ($updateParentSummary) {
                PurchaseInvoiceActions::updateSummary($purchaseInvoiceItem->purchaseInvoice);
                $purchaseInvoiceItem->refresh();
            }

            $this->flushCache();

            return $purchaseInvoiceItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(PurchaseInvoiceItem $purchaseInvoiceItem, PurchaseInvoiceItemUpdateDTO $data, bool $updateParentSummary = true): PurchaseInvoiceItem
    {
        $timer_start = microtime(true);

        try {
            $purchaseInvoiceItem->purchase_order_item_id = $data->purchaseOrderItemId;

            $this->fillCalculatedColumns($purchaseInvoiceItem, [
                'qty' => $data->qty,
                'productUnitId' => $data->productUnitId,
                'productUnitConversionValue' => $data->productUnitConversionValue,
                'productUnitPrice' => $data->productUnitPrice,
                'productUnitIsPriceIncludeVat' => $data->productUnitIsPriceIncludeVat,
                'priceDiscount' => $data->priceDiscount,
                'subtotalDiscount' => $data->subtotalDiscount,
                'vatProfileId' => $data->vatProfileId,
                'vatRate' => $data->vatRate,
                'vatBaseNumerator' => $data->vatBaseNumerator,
                'vatBaseDenominator' => $data->vatBaseDenominator,
                'remarks' => $data->remarks,
            ]);

            $purchaseInvoiceItem->save();

            if ($updateParentSummary) {
                PurchaseInvoiceActions::updateSummary($purchaseInvoiceItem->purchaseInvoice);
                $purchaseInvoiceItem->refresh();
            }

            $this->flushCache();

            return $purchaseInvoiceItem;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function delete(PurchaseInvoiceItem $purchaseInvoiceItem, bool $updateParentSummary = true): bool
    {
        $timer_start = microtime(true);
        $retval = false;

        try {
            $purchaseInvoice = $purchaseInvoiceItem->purchaseInvoice;

            $retval = $purchaseInvoiceItem->delete();

            if ($updateParentSummary) {
                PurchaseInvoiceActions::updateSummary($purchaseInvoice);
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
     * Fill the user-input columns and the row-level calculated waterfall
     * (discounts are direct nominal inputs; global discount, VAT chain and
     * cogs are recomputed later by PurchaseInvoiceActions::updateSummary).
     *
     * @param  array<string, mixed>  $data
     */
    private function fillCalculatedColumns(PurchaseInvoiceItem $purchaseInvoiceItem, array $data): void
    {
        $purchaseInvoiceItem->qty = $data['qty'];
        $purchaseInvoiceItem->product_unit_id = $data['productUnitId'];
        $purchaseInvoiceItem->product_id = ProductUnit::query()->whereKey($data['productUnitId'])->value('product_id');
        $purchaseInvoiceItem->product_unit_conversion_value = $data['productUnitConversionValue'];
        $purchaseInvoiceItem->product_unit_qty_base = $data['qty'] * $data['productUnitConversionValue'];
        $purchaseInvoiceItem->product_unit_price = $data['productUnitPrice'];
        $purchaseInvoiceItem->product_unit_is_price_include_vat = $data['productUnitIsPriceIncludeVat'];
        $purchaseInvoiceItem->vat_profile_id = $data['vatProfileId'];
        $purchaseInvoiceItem->vat_rate = $data['vatRate'];
        $purchaseInvoiceItem->vat_base_numerator = $data['vatBaseNumerator'];
        $purchaseInvoiceItem->vat_base_denominator = $data['vatBaseDenominator'];
        $purchaseInvoiceItem->remarks = $data['remarks'];

        $purchaseInvoiceItem->price_discount = min((float) $data['priceDiscount'], (float) $data['productUnitPrice']);
        $purchaseInvoiceItem->price_after_discount = max(
            0,
            (float) $purchaseInvoiceItem->product_unit_price - (float) $purchaseInvoiceItem->price_discount
        );
        $purchaseInvoiceItem->subtotal = (float) $purchaseInvoiceItem->qty * (float) $purchaseInvoiceItem->price_after_discount;
        $purchaseInvoiceItem->subtotal_discount = min((float) $data['subtotalDiscount'], (float) $purchaseInvoiceItem->subtotal);
        $purchaseInvoiceItem->subtotal_after_discount = max(
            0,
            (float) $purchaseInvoiceItem->subtotal - (float) $purchaseInvoiceItem->subtotal_discount
        );
    }
}
