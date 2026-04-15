<?php

namespace App\Actions\PurchaseOrderItem;

use App\Models\PurchaseOrder;

class PurchaseOrderItemCalculationActions
{
    /**
     * Finalize calculated fields for purchase order items.
     *
     * Call this after the purchase order, its items, discounts, and header
     * summary have been saved.
     *
     * Calculation order:
     * global_discount -> subtotal_after_global_discount -> vat_base -> vat
     * -> rounding -> grand_total -> cogs -> total_cogs -> base_unit_cogs
     */
    public function updateCalculatedFieldsByPurchaseOrder(PurchaseOrder $purchaseOrder): void
    {
        foreach ($purchaseOrder->items as $poItem) {
            $globalDiscount = (function () use ($poItem, $purchaseOrder) {
                $itemTotalBeforeGlobalDiscount = (float) $purchaseOrder->item_total_before_global_discount;
                $purchaseOrderGlobalDiscount = (float) $purchaseOrder->global_discount;

                if ($itemTotalBeforeGlobalDiscount <= 0 || $purchaseOrderGlobalDiscount <= 0) return 0;

                $value = ((float) $poItem->subtotal_after_discount / $itemTotalBeforeGlobalDiscount) * $purchaseOrderGlobalDiscount;

                return $value < 0 ? 0 : $value;
            })();

            $subtotalAfterGlobalDiscount = (function () use ($poItem, $globalDiscount) {
                return max((float) $poItem->subtotal_after_discount - $globalDiscount, 0);
            })();

            $vatBaseFactor = (function () use ($poItem) {
                if ($poItem->vat_base_denominator <= 0) return 0;

                return (float) $poItem->vat_base_numerator / (float) $poItem->vat_base_denominator;
            })();

            $vatBase = (function () use ($poItem, $subtotalAfterGlobalDiscount, $vatBaseFactor) {
                $vatRate = (float) $poItem->vat_rate;

                if ($subtotalAfterGlobalDiscount <= 0 || $vatRate <= 0 || $vatBaseFactor <= 0) return 0;

                if ($poItem->product_unit_is_price_include_vat) {
                    $subtotalAfterGlobalDiscount = $subtotalAfterGlobalDiscount / (1 + ($vatRate / 100));
                }

                return $subtotalAfterGlobalDiscount * $vatBaseFactor;
            })();

            $vat = (function () use ($poItem, $vatBase) {
                $vatRate = (float) $poItem->vat_rate;

                if ($vatBase <= 0 || $vatRate <= 0) return 0;

                $value = $vatBase * ($vatRate / 100);

                return $value < 0 ? 0 : $value;
            })();

            $poItem->global_discount = $globalDiscount;
            $poItem->subtotal_after_global_discount = $subtotalAfterGlobalDiscount;
            $poItem->vat_base = $vatBase;
            $poItem->vat = $vat;
        }

        $itemTotalBeforeRounding = $purchaseOrder->items->sum(function ($poItem) {
            return (float) $poItem->subtotal_after_global_discount + (float) $poItem->vat;
        });

        foreach ($purchaseOrder->items as $poItem) {
            $poItem->rounding = (function () use ($poItem, $purchaseOrder, $itemTotalBeforeRounding) {
                $poItemTotalBeforeRounding = (float) $poItem->subtotal_after_global_discount + (float) $poItem->vat;
                $purchaseOrderRounding = (float) $purchaseOrder->rounding;

                if ($itemTotalBeforeRounding <= 0 || $purchaseOrderRounding == 0 || $poItemTotalBeforeRounding <= 0) return 0;

                return ($poItemTotalBeforeRounding / $itemTotalBeforeRounding) * $purchaseOrderRounding;
            })();

            $poItem->grand_total = (function () use ($poItem) {
                return (float) $poItem->subtotal_after_global_discount + (float) $poItem->vat + (float) $poItem->rounding;
            })();

            $poItem->cogs = (function () use ($poItem) {
                $qty = (float) $poItem->qty;
                $grandTotal = (float) $poItem->grand_total;

                if ($qty <= 0 || $grandTotal <= 0) return 0;

                return $grandTotal / $qty;
            })();

            $poItem->total_cogs = (function () use ($poItem) {
                $qty = (float) $poItem->qty;
                $cogs = (float) $poItem->cogs;

                if ($qty <= 0 || $cogs <= 0) return 0;

                return $qty * $cogs;
            })();

            $poItem->base_unit_cogs = (function () use ($poItem) {
                $productUnitQtyBase = (float) $poItem->product_unit_qty_base;
                $totalCogs = (float) $poItem->total_cogs;

                if ($productUnitQtyBase <= 0 || $totalCogs <= 0) return 0;

                return $totalCogs / $productUnitQtyBase;
            })();

            $poItem->save();
        }
    }
}
